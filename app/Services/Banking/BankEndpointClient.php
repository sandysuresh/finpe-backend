<?php

namespace App\Services\Banking;

use App\Models\Bank;
use App\Models\BankApiEndpoint;
use App\Support\OutboundUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class BankEndpointClient
{
    public function dispatch(Bank $bank, BankApiEndpoint $endpoint, array $payload): array
    {
        if ($bank->driver === 'simulator') {
            return [
                'success' => true,
                'data' => $endpoint->sample_response['data'] ?? $endpoint->sample_response ?? ['status' => 'success'],
                'message' => 'Simulator response',
            ];
        }

        if (! $bank->base_url) {
            return ['success' => false, 'message' => 'Bank API base URL is not configured.'];
        }

        try {
            OutboundUrl::assertSafe($bank->base_url, app()->environment('local'));
            $url = OutboundUrl::join($bank->base_url, (string) ($endpoint->bank_path ?: $endpoint->slug));
        } catch (InvalidArgumentException $e) {
            Log::warning('bank_url_rejected', ['bank_id' => $bank->id, 'reason' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Bank API URL is not allowed.'];
        }

        $headers = $this->headers($bank);

        try {
            $request = Http::timeout(15)
                ->connectTimeout(5)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders($headers);
            $response = strtoupper($endpoint->method) === 'GET'
                ? $request->get($url, $payload)
                : $request->asJson()->send($endpoint->method, $url, ['json' => $payload]);

            $body = $response->json();
            if (! is_array($body)) {
                $body = ['raw' => mb_substr($response->body(), 0, 2000)];
            }

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Bank request failed.',
                    'data' => null,
                ];
            }

            return [
                'success' => (bool) ($body['success'] ?? true),
                'message' => is_string($body['message'] ?? null) ? $body['message'] : null,
                'data' => $body['data'] ?? $body,
            ];
        } catch (Throwable $e) {
            Log::warning('bank_http_error', ['bank_id' => $bank->id, 'error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Bank request failed.',
            ];
        }
    }

    private function headers(Bank $bank): array
    {
        $headers = ['Accept' => 'application/json'];

        if ($bank->api_key) {
            $headers['X-API-Key'] = $bank->api_key;
        }
        if ($bank->api_secret) {
            $headers['X-API-Secret'] = $bank->api_secret;
        }
        if ($bank->username && $bank->password) {
            $headers['Authorization'] = 'Basic '.base64_encode($bank->username.':'.$bank->password);
        }

        return $headers;
    }
}
