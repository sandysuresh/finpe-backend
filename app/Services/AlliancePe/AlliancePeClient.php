<?php

namespace App\Services\AlliancePe;

use App\Exceptions\AlliancePeException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class AlliancePeClient
{
    public function environment(): string
    {
        $environment = strtolower(trim((string) config('alliancepe.environment', '')));
        if (! in_array($environment, ['uat', 'live'], true)) {
            throw new AlliancePeException('AlliancePe environment must be uat or live.', 500);
        }

        return $environment;
    }

    public function baseUrl(): string
    {
        $url = config('alliancepe.'.$this->environment().'.base_url');
        if (! is_string($url) || trim($url) === '') {
            throw new AlliancePeException('AlliancePe '.strtoupper($this->environment()).' base URL is not configured.', 500);
        }

        return rtrim(trim($url), '/');
    }

    public function endpoint(string $path): string
    {
        $path = '/'.ltrim($path, '/');
        $base = $this->baseUrl();
        if (str_ends_with($base, '/api') && str_starts_with($path, '/api/')) {
            $path = substr($path, 4);
        }

        return $base.$path;
    }

    /**
     * @return array{available: float|int|string|null, currency: mixed}
     */
    public function balance(): array
    {
        $response = $this->send('GET', '/wallet/balance');

        return is_array($response->json()) ? $response->json() : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{status: int, body: array<string, mixed>, retry_after: int|null}
     */
    public function createPayout(array $payload): array
    {
        return $this->exchange('POST', '/payouts', $payload);
    }

    /**
     * @return array{status: int, body: array<string, mixed>, retry_after: int|null}
     */
    public function payout(string $payoutId): array
    {
        return $this->exchange('GET', '/payouts/'.rawurlencode($payoutId));
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array{status: int, body: array<string, mixed>, retry_after: int|null}
     */
    public function payouts(array $query = []): array
    {
        return $this->exchange('GET', '/payouts', null, $query);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, scalar|null>  $query
     * @return array{status: int, body: array<string, mixed>, retry_after: int|null}
     */
    private function exchange(string $method, string $path, ?array $body = null, array $query = []): array
    {
        $response = $this->send($method, $path, $body, $query);
        $decoded = $response->json();

        return [
            'status' => $response->status(),
            'body' => is_array($decoded) ? $decoded : [],
            'retry_after' => $this->retryAfter($response),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, scalar|null>  $query
     */
    private function send(string $method, string $path, ?array $body = null, array $query = []): Response
    {
        try {
            $pending = $this->http();
            $url = $this->endpoint($path);
            $response = match ($method) {
                'GET' => $pending->get($url, array_filter($query, fn ($value) => $value !== null && $value !== '')),
                'POST' => $pending->post($url, $body ?? []),
                default => throw new AlliancePeException('Unsupported AlliancePe method.', 500),
            };
        } catch (AlliancePeException $e) {
            throw $e;
        } catch (Throwable) {
            throw new AlliancePeException('AlliancePe could not be reached. Reconcile with the same merchant order id.', 503, 'GATEWAY_UNAVAILABLE');
        }

        if ($response->status() === 429) {
            throw new AlliancePeException('AlliancePe rate limit reached.', 429, 'RATE_LIMIT', $this->retryAfter($response));
        }

        return $response;
    }

    private function http(): PendingRequest
    {
        $key = config('alliancepe.'.$this->environment().'.api_key');
        if (! is_string($key) || $key === '') {
            throw new AlliancePeException('AlliancePe '.strtoupper($this->environment()).' API key is not configured.', 500);
        }

        return Http::timeout((int) config('alliancepe.timeout', 30))
            ->connectTimeout((int) config('alliancepe.connect_timeout', 10))
            ->withOptions(['allow_redirects' => false])
            ->acceptJson()
            ->asJson()
            ->withToken($key);
    }

    private function retryAfter(Response $response): ?int
    {
        $value = $response->header('Retry-After');
        if (! is_string($value) || ! ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }
}
