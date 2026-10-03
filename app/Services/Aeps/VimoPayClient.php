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
    /** @var array{http_status: int|null, message: string|null} */
    private array $authorizationMeta = ['http_status' => null, 'message' => null];

    public function __construct(private VimoPayCipher $cipher) {}

    /**
     * Run the existing authorize request and return a display-safe result.
     * The bearer token is never included.
     *
     * @return array{http_status: int|null, success: bool, message: string, token_received: bool, response_ms: int}
     */
    public function testAuthorization(): array
    {
        $started = hrtime(true);
        $this->authorizationMeta = ['http_status' => null, 'message' => null];

        try {
            $token = $this->authorize();
        } catch (AepsException $e) {
            return $this->authorizationResult(false, $e->getMessage(), false, $started);
        } catch (Throwable) {
            return $this->authorizationResult(false, 'Authorization failed.', false, $started);
        }

        $received = is_string($token) && $token !== '';
        $message = $this->safeAuthorizationMessage($this->authorizationMeta['message'], $received ? $token : null);
        unset($token);
        if ($message === '') {
            $message = 'Authorization succeeded.';
        }

        return $this->authorizationResult(true, $message, $received, $started);
    }

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

        if ($path !== 'authorize' && isset($json['data']) && is_string($json['data']) && $json['data'] !== '') {
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

        $this->authorizationMeta['http_status'] = $response->status();
        $json = $this->decode($response, 'authorize');
        $rawMessage = $json['message'] ?? null;
        $this->authorizationMeta['message'] = is_string($rawMessage) ? $rawMessage : null;
        $token = $json['data'] ?? null;
        if (! is_string($token) || $token === '') {
            $keys = is_array($token) ? implode(',', array_keys($token)) : 'none';
            Log::warning('aeps_provider_token_missing', ['keys' => $keys]);

            throw new AepsException('AePS provider authorization did not return a token.', 502);
        }

        return $token;
    }

    /**
     * @return array{http_status: int|null, success: bool, message: string, token_received: bool, response_ms: int}
     */
    private function authorizationResult(bool $success, string $message, bool $tokenReceived, int $started): array
    {
        return [
            'http_status' => $this->authorizationMeta['http_status'],
            'success' => $success,
            'message' => $message !== '' ? $message : ($success ? 'Authorization succeeded.' : 'Authorization failed.'),
            'token_received' => $tokenReceived,
            'response_ms' => (int) round((hrtime(true) - $started) / 1_000_000),
        ];
    }

    private function safeAuthorizationMessage(mixed $message, ?string $token): string
    {
        if (! is_string($message)) {
            return '';
        }

        $message = trim($message);
        if ($message === '') {
            return '';
        }

        if ($token !== null && $token !== '' && str_contains($message, $token)) {
            return '';
        }

        if (preg_match('/bearer|secret|salt|encrypt|token|api[_-]?key|password/i', $message) === 1 || strlen($message) > 180) {
            return '';
        }

        return $message;
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
            'payout_banks' => '/masterapi/api/master/banklistuat',
            'payout_purposes' => '/masterapi/api/master/purposelistuat',
            'payout_states' => '/masterapi/api/master/statelistuat',
            'payout_transfer' => '/payoutapi/api/payment/payoutsuat',
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
        return $this->config('secret_key');
    }

    private function ivKey(): string
    {
        return $this->config('salt_key');
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
