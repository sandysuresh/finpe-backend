<?php

namespace App\Services\Aeps;

use App\Exceptions\AepsException;
use App\Support\OutboundUrl;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use ReflectionProperty;
use Throwable;

class VimoPayClient
{
    /** @var array{http_status: int|null, message: string|null} */
    private array $authorizationMeta = ['http_status' => null, 'message' => null];

    private bool $payoutRequest = false;

    private ?int $lastHttpStatus = null;

    public function __construct(private VimoPayCipher $cipher) {}

    /**
     * Payout authorization probe. Uses the payout authorize route, not the AePS authorize operation.
     * The bearer token is never included.
     *
     * @return array{http_status: int|null, success: bool, message: string, token_received: bool, response_ms: int}
     */
    public function testAuthorization(): array
    {
        $started = hrtime(true);
        $this->authorizationMeta = ['http_status' => null, 'message' => null];
        $previous = $this->payoutRequest;
        $this->payoutRequest = true;

        try {
            $token = $this->authorize();
        } catch (AepsException $e) {
            return $this->authorizationResult(false, $e->getMessage(), false, $started);
        } catch (Throwable) {
            return $this->authorizationResult(false, 'Authorization failed.', false, $started);
        } finally {
            $this->payoutRequest = $previous;
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
        $plaintext = json_encode($plainBody, JSON_UNESCAPED_SLASHES);
        $capture = $this->isPayoutTransferPath($path);
        $encrypted = $this->cipher->encrypt(
            $plaintext === false ? '' : $plaintext,
            $this->encryptKey(),
            $this->ivKey(),
        );

        try {
            $decoded = $this->send('POST', $path, ['requestBody' => $encrypted]);
        } catch (Throwable $e) {
            if ($capture) {
                $this->writePayoutExchange($path, $plainBody, null, $e);
            }
            throw $e;
        }

        if ($capture) {
            $this->writePayoutExchange($path, $plainBody, $decoded, null);
        }

        return $decoded;
    }

    private function send(string $method, string $path, ?array $body): array
    {
        $previous = $this->payoutRequest;
        $this->payoutRequest = $this->usesLivePayoutAuthorization($path);
        $tokenKey = '';
        try {
            $tokenKey = $this->tokenCacheKey();
            $url = $this->url($path);
            $request = Http::timeout(25)
                ->connectTimeout(5)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders($this->authorizedHeaders());
            $response = $method === 'GET'
                ? $request->get($url)
                : $request->acceptJson()->asJson()->post($url, $body ?? []);
        } catch (AepsException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('aeps_provider_http_failed', ['path' => $path]);

            throw new AepsException('AePS provider request failed.', 502);
        } finally {
            $this->payoutRequest = $previous;
        }

        $this->lastHttpStatus = $response->status();

        if ($response->status() === 401) {
            Cache::forget($tokenKey);
            throw new AepsException('AePS provider authorization failed.', 502);
        }

        return $this->decode($response, $path);
    }

    private function decode(Response $response, string $path): array
    {
        $json = $response->json();
        if (! is_array($json)) {
            Log::warning('aeps_provider_invalid_response', $this->invalidResponseLog($response));

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
        $url = $this->url($this->path($this->payoutRequest ? 'payout_authorize' : 'authorize'));
        try {
            $response = $this->isLivePayoutAuthorization()
                ? $this->sendLiveAuthorization($url)
                : $this->sendExistingAuthorization($url);
        } catch (Throwable $e) {
            Log::warning('aeps_provider_auth_failed', [
                'exception' => $e::class,
                'diagnostic' => $this->sanitizedAuthDiagnostic($e),
            ]);

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

    private function isLivePayoutAuthorization(): bool
    {
        return $this->payoutRequest
            && strtolower(trim((string) config('services.vimopay.environment'))) === 'live';
    }

    /**
     * @return array<string, string>
     */
    private function authorizationCredentialHeaders(): array
    {
        return [
            'secretKey' => $this->config('secret_key'),
            'saltKey' => $this->config('salt_key'),
            'encryptdecryptKey' => $this->config('encrypt_key'),
            'userId' => $this->config('user_id'),
        ];
    }

    private function sendExistingAuthorization(string $url): Response
    {
        return Http::timeout(20)
            ->connectTimeout(5)
            ->withOptions(['allow_redirects' => false])
            ->withHeaders($this->authorizationCredentialHeaders() + ['Accept' => 'application/json'])
            ->post($url);
    }

    private function sendLiveAuthorization(string $url): Response
    {
        $pending = Http::timeout(20)
            ->connectTimeout(5)
            ->withOptions(['allow_redirects' => false])
            ->bodyFormat('body')
            ->withHeaders($this->authorizationCredentialHeaders());

        $optionsProperty = new ReflectionProperty(PendingRequest::class, 'options');
        $options = $optionsProperty->getValue($pending);
        unset($options['headers']['Content-Type'], $options['headers']['Accept']);
        $optionsProperty->setValue($pending, $options);
        (new ReflectionProperty(PendingRequest::class, 'pendingBody'))->setValue($pending, '');

        return $pending->send('POST', $url);
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
        if (preg_match('#^https://#i', $path) === 1) {
            $url = $path;
        } else {
            $base = rtrim($this->config('base_url'), '/');
            $url = $base.'/'.ltrim($path, '/');
        }
        try {
            OutboundUrl::assertSafe($url, app()->environment('local'));
        } catch (InvalidArgumentException $e) {
            throw new AepsException('AePS provider URL is not allowed.', 500);
        }

        return $url;
    }

    public function path(string $name): string
    {
        if (strtolower(trim((string) config('services.vimopay.environment'))) === 'live') {
            $configured = config('payout.live_endpoints.'.$name);
            if (is_string($configured) && $configured !== '') {
                return $configured;
            }
        }

        if ($this->config('environment') !== 'uat') {
            throw new AepsException('Only the UAT paths from VimoPay specification v1.0.13 are configured.', 500);
        }

        return match ($name) {
            'authorize' => '/aepsapi/api/signature/authorizeuat',
            'payout_authorize' => '/aepsapi/api/signature/authorizeuat',
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
        $environment = strtolower(trim($this->config('environment')));
        $channel = $this->payoutRequest ? 'payout' : 'aeps';

        return 'vimopay:'.$environment.':'.$channel.':token:'.hash('sha256', $this->config('user_id'));
    }

    /**
     * @return array{provider: string, environment: string, http_status: int, content_type: ?string, body_bytes: int, request_id?: string, body_preview?: string}
     */
    private function invalidResponseLog(Response $response): array
    {
        $context = [
            'provider' => 'vimopay',
            'environment' => strtolower(trim((string) config('services.vimopay.environment'))),
            'http_status' => $response->status(),
            'content_type' => $this->safeResponseHeader($response, 'Content-Type'),
            'body_bytes' => strlen($response->body()),
        ];
        $requestId = $this->safeRequestId($response);
        if ($requestId !== null) {
            $context['request_id'] = $requestId;
        }
        $preview = $this->redactedBodyPreview($response->body());
        if ($preview !== null) {
            $context['body_preview'] = $preview;
        }

        return $context;
    }

    private function safeResponseHeader(Response $response, string $name): ?string
    {
        $value = $this->firstHeader($response, $name);
        if ($value === null || strlen($value) > 120 || preg_match('/^[\w.+\/;=\- ]+$/', $value) !== 1) {
            return null;
        }
        foreach ($this->configuredSecrets() as $secret) {
            if (str_contains($value, $secret)) {
                return null;
            }
        }

        return $value;
    }

    private function safeRequestId(Response $response): ?string
    {
        foreach (['X-Request-Id', 'X-Correlation-Id'] as $name) {
            $value = $this->firstHeader($response, $name);
            if ($value !== null && preg_match('/^[A-Za-z0-9._:-]{1,80}$/', $value) === 1) {
                return $value;
            }
        }

        return null;
    }

    private function firstHeader(Response $response, string $name): ?string
    {
        $value = $response->header($name);
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function redactedBodyPreview(string $body): ?string
    {
        if ($body === '' || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $body) === 1 || ! mb_check_encoding($body, 'UTF-8')) {
            return null;
        }
        $secrets = $this->configuredSecrets();
        if ($secrets === null) {
            return null;
        }
        $preview = substr($body, 0, 160);
        if (! mb_check_encoding($preview, 'UTF-8')) {
            return null;
        }
        foreach ($secrets as $secret) {
            $position = strpos($body, $secret);
            if ($position !== false && $position < 160 && $position + strlen($secret) > 160) {
                return null;
            }
        }
        foreach ($secrets as $secret) {
            $preview = str_replace($secret, '[redacted]', $preview);
        }
        $preview = preg_replace('/Bearer\s+\S+/i', 'Bearer [redacted]', $preview) ?? '';
        if (preg_match('/(secret|salt|encrypt|token|authorization|api[_-]?key|password|bearer)\s*[=:]\s*\S+/i', $preview) === 1) {
            return null;
        }
        foreach ($secrets as $secret) {
            if (str_contains($preview, $secret)) {
                return null;
            }
        }
        $preview = trim($preview);

        return $preview === '' ? null : $preview;
    }

    /**
     * @return list<string>|null
     */
    private function configuredSecrets(): ?array
    {
        $secrets = [];
        foreach (['user_id', 'secret_key', 'salt_key', 'encrypt_key', 'iv_key'] as $key) {
            $value = config('services.vimopay.'.$key);
            if (! is_string($value) || $value === '') {
                continue;
            }
            if (strlen($value) < 8) {
                return null;
            }
            $secrets[] = $value;
        }

        return $secrets;
    }

    private function sanitizedAuthDiagnostic(Throwable $e): string
    {
        $message = $e->getMessage();
        foreach (['user_id', 'secret_key', 'salt_key', 'encrypt_key', 'iv_key'] as $key) {
            $value = config('services.vimopay.'.$key);
            if (is_string($value) && $value !== '') {
                $message = str_replace($value, '[redacted]', $message);
            }
        }

        $message = preg_replace('/Bearer\s+\S+/i', 'Bearer [redacted]', $message) ?? '';
        $message = preg_replace('/\?[^\s]*/', '', $message) ?? '';
        $message = trim($message);
        if ($message === '' || strlen($message) > 180) {
            return $e::class;
        }

        return $message;
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

    private function isPayoutTransferPath(string $path): bool
    {
        $live = config('payout.live_endpoints.payout_transfer');
        if (is_string($live) && $live !== '' && $path === $live) {
            return true;
        }

        return str_ends_with(strtolower($path), 'payoutsuat');
    }

    /**
     * @param  array<string, mixed>|null  $decoded
     */
    private function writePayoutExchange(string $path, array $plainBody, ?array $decoded, ?Throwable $error): void
    {
        $location = is_string($plainBody['beneficiaryLocation'] ?? null) ? $plainBody['beneficiaryLocation'] : '';
        Log::channel('vimopay')->info('vimopay_payout_exchange', [
            'method' => 'POST',
            'endpoint' => $path,
            'plaintext_parameters' => json_encode($this->maskPayoutTree($plainBody), JSON_UNESCAPED_SLASHES),
            'beneficiaryLocation_hex' => bin2hex($location),
            'beneficiaryLocation_length' => strlen($location),
            'http_status' => $this->lastHttpStatus,
            'provider_response' => $decoded === null ? null : $this->maskPayoutTree($decoded),
            'error' => $error === null ? null : $error::class,
        ]);
    }

    private function maskPayoutTree(mixed $value, ?string $key = null): mixed
    {
        if (is_array($value)) {
            $masked = [];
            foreach ($value as $childKey => $child) {
                $masked[$childKey] = $this->maskPayoutTree($child, is_string($childKey) ? $childKey : null);
            }

            return $masked;
        }

        if (is_string($key) && preg_match('/account|mobile|secret|salt|token|authorization|bearer|password|encrypt|aadhaar|otp/i', $key) === 1) {
            return $this->maskSecret($value);
        }

        if (is_string($value) && strlen($value) > 180 && preg_match('/^[A-Za-z0-9+\/=]+$/', $value) === 1) {
            return '[redacted]';
        }

        return $value;
    }

    private function maskSecret(mixed $value): string
    {
        if (! is_string($value)) {
            return '[redacted]';
        }
        $length = strlen($value);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4).substr($value, -4);
    }

    private function usesLivePayoutAuthorization(string $path): bool
    {
        if (strtolower(trim((string) config('services.vimopay.environment'))) !== 'live') {
            return false;
        }

        $endpoints = config('payout.live_endpoints');
        if (! is_array($endpoints)) {
            return false;
        }

        unset($endpoints['payout_authorize']);

        return in_array($path, $endpoints, true);
    }
}
