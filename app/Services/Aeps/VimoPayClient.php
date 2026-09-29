<?php

namespace App\Services\Aeps;

use App\Exceptions\AepsException;
use App\Support\OutboundUrl;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class VimoPayClient
{
    public function __construct(private VimoPayCipher $cipher) {}

    public function get(string $path): array
    {
        return $this->send('GET', $path, null);
    }

    public function post(string $path, array $plainBody): array
    {
        $encrypted = $this->cipher->encrypt(
            json_encode($plainBody, JSON_UNESCAPED_SLASHES),
            $this->encryptKey(),
            $this->ivKey(),
        );

        return $this->send('POST', $path, ['requestBody' => $encrypted]);
    }

    private function send(string $method, string $path, ?array $body): array
    {
        $url = $this->url($path);
        try {
            $request = Http::timeout(25)
                ->connectTimeout(5)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders($this->authorizedHeaders());
            $response = $method === 'GET'
                ? $request->get($url)
                : $request->acceptJson()->asJson()->post($url, $body ?? []);
        } catch (Throwable $e) {
            Log::warning('aeps_provider_http_failed', ['path' => $path]);

            throw new AepsException('AePS provider request failed.', 502);
        }

        if ($response->status() === 401) {
            Cache::forget($this->tokenCacheKey());
            throw new AepsException('AePS provider authorization failed.', 502);
        }

        return $this->decode($response, $path);
    }

    private function decode(Response $response, string $path): array
    {
        $json = $response->json();
        if (! is_array($json)) {
            Log::warning('aeps_provider_invalid_response', ['path' => $path, 'http_status' => $response->status()]);

            throw new AepsException('AePS provider returned an unreadable response.', 502);
        }

        if (isset($json['data']) && is_string($json['data']) && $json['data'] !== '') {
            try {
                $plain = $this->cipher->decrypt($json['data'], $this->encryptKey(), $this->ivKey());
            } catch (Throwable) {
                throw new AepsException('AePS provider response could not be decrypted.', 502);
            }
            $decoded = json_decode($plain, true);
            $json['data'] = is_array($decoded) ? $decoded : $plain;
        }

        return $json;
    }

    private function authorizedHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token(),
            'userId' => $this->config('user_id'),
            'Accept' => 'application/json',
        ];
    }

    private function token(): string
    {
        $cached = Cache::get($this->tokenCacheKey());
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $token = $this->authorize();
        Cache::put($this->tokenCacheKey(), $token, now()->addMinutes(10));

        return $token;
    }

    private function authorize(): string
    {
        $url = $this->url($this->path('authorize'));
        try {
            $response = Http::timeout(20)
                ->connectTimeout(5)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders([
                    'secretKey' => $this->config('secret_key'),
                    'saltKey' => $this->config('salt_key'),
                    'encryptdecryptKey' => $this->config('encrypt_key'),
                    'userId' => $this->config('user_id'),
                    'Accept' => 'application/json',
                ])
                ->post($url);
        } catch (Throwable) {
            Log::warning('aeps_provider_auth_failed');

            throw new AepsException('AePS provider authorization failed.', 502);
        }

        $json = $this->decode($response, 'authorize');
        $token = $this->extractToken($json['data'] ?? null);
        if ($token === null) {
            $keys = is_array($json['data'] ?? null) ? implode(',', array_keys($json['data'])) : 'none';
            Log::warning('aeps_provider_token_missing', ['keys' => $keys]);

            throw new AepsException('AePS provider authorization did not return a token.', 502);
        }

        return $token;
    }

    private function extractToken(mixed $data): ?string
    {
        if (is_string($data) && $data !== '') {
            return $data;
        }
        if (! is_array($data)) {
            return null;
        }
        foreach (['token', 'accessToken', 'access_token', 'authToken', 'bearerToken', 'jwt'] as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
        }
        if (isset($data['data'])) {
            return $this->extractToken($data['data']);
        }

        return null;
    }

    private function url(string $path): string
    {
        $base = rtrim($this->config('base_url'), '/');
        $url = $base.'/'.ltrim($path, '/');
        try {
            OutboundUrl::assertSafe($url, app()->environment('local'));
        } catch (InvalidArgumentException $e) {
            throw new AepsException('AePS provider URL is not allowed.', 500);
        }

        return $url;
    }

    public function path(string $name): string
    {
        if ($this->config('environment') !== 'uat') {
            throw new AepsException('Only the UAT paths from VimoPay specification v1.0.13 are configured.', 500);
        }

        return match ($name) {
            'authorize' => '/aepsapi/api/signature/authorizeuat',
            'banks' => '/masterapi/api/master/banklistuat',
            'bank_iin' => '/aepsapi/api/payment/bankiinuat',
            'states' => '/masterapi/api/master/statelistuat',
            'districts' => '/aepsapi/api/payment/acquiredistrictuat',
            'register' => '/aepsapi/api/payment/merchantonboarduat',
            'send_otp' => '/aepsapi/api/payment/sendotpuat',
            'resend_otp' => '/aepsapi/api/payment/resendotpuat',
            'verify_otp' => '/aepsapi/api/payment/validateotpuat',
            'ekyc' => '/aepsapi/api/payment/merchantekycuat',
            'two_factor' => '/aepsapi/api/payment/2fauat',
            'transaction_otp' => '/aepsapi/api/Payment/AepsTransactionOtpuat',
            'transact' => '/aepsapi/api/payment/aepsuat',
            default => throw new AepsException('Unknown AePS provider operation.', 500),
        };
    }

    private function tokenCacheKey(): string
    {
        return 'aeps:vimopay:token:'.hash('sha256', $this->config('user_id'));
    }

    private function encryptKey(): string
    {
        return $this->config('encrypt_key');
    }

    private function ivKey(): string
    {
        return $this->config('iv_key');
    }

    private function config(string $key): string
    {
        $value = config('services.vimopay.'.$key);
        if (! is_string($value) || $value === '') {
            throw new AepsException('AePS provider is not configured.', 500);
        }

        return $value;
    }
}
