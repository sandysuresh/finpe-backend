<?php

namespace App\Services\Banking;

use App\Models\Bank;
use App\Models\Transaction;
use App\Support\OutboundUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class HttpBankGateway implements BankGateway
{
    public function testConnection(Bank $bank): BankPayoutResult
    {
        if (! $bank->base_url) {
            return new BankPayoutResult('failed', null, 'Base URL is required for HTTP bank driver.');
        }

        try {
            OutboundUrl::assertSafe($bank->base_url, app()->environment('local'));
            $url = OutboundUrl::join($bank->base_url, '/health');
        } catch (InvalidArgumentException $e) {
            return new BankPayoutResult('failed', null, 'Bank API URL is not allowed.');
        }

        try {
            $response = Http::timeout(12)
                ->connectTimeout(5)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders($this->headers($bank))
                ->get($url);

            if ($response->successful()) {
                return new BankPayoutResult('success', null, 'Bank health check passed.');
            }

            return new BankPayoutResult('failed', null, 'Bank health returned HTTP '.$response->status());
        } catch (Throwable $e) {
            Log::warning('bank_health_error', ['bank_id' => $bank->id, 'error' => $e->getMessage()]);

            return new BankPayoutResult('failed', null, 'Bank health check failed.');
        }
    }

    public function payout(Bank $bank, Transaction $transaction): BankPayoutResult
    {
        if (! $bank->base_url) {
            return new BankPayoutResult('failed', null, 'Bank API base URL is not configured.');
        }

        try {
            OutboundUrl::assertSafe($bank->base_url, app()->environment('local'));
            $url = OutboundUrl::join($bank->base_url, '/payout');
        } catch (InvalidArgumentException $e) {
            return new BankPayoutResult('failed', null, 'Bank API URL is not allowed.');
        }

        try {
            $response = Http::timeout(25)
                ->connectTimeout(5)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders($this->headers($bank))
                ->post($url, [
                    'reference' => $transaction->reference,
                    'amount' => (float) $transaction->amount,
                    'service' => strtoupper((string) $transaction->service),
                    'beneficiary_name' => $transaction->beneficiary_name,
                    'account_number' => $transaction->account_number,
                    'ifsc_code' => $transaction->ifsc_code,
                    'bank_name' => $transaction->bank_name,
                    'remarks' => $transaction->remarks,
                ]);

            $payload = $response->json() ?? [];
            $status = strtolower((string) ($payload['status'] ?? ($response->successful() ? 'success' : 'failed')));

            if (! in_array($status, ['success', 'pending', 'failed'], true)) {
                $status = $response->successful() ? 'pending' : 'failed';
            }

            return new BankPayoutResult(
                $status,
                $payload['bank_reference'] ?? $payload['utr'] ?? null,
                $payload['message'] ?? ('HTTP '.$response->status()),
                is_array($payload) ? $payload : [],
            );
        } catch (Throwable $e) {
            Log::warning('bank_payout_error', ['bank_id' => $bank->id, 'error' => $e->getMessage()]);

            return new BankPayoutResult('failed', null, 'Bank request failed.');
        }
    }

    public function status(Bank $bank, Transaction $transaction): BankPayoutResult
    {
        if (! $bank->base_url) {
            return new BankPayoutResult($transaction->status, $transaction->bank_reference, 'Bank API base URL is not configured.');
        }

        try {
            OutboundUrl::assertSafe($bank->base_url, app()->environment('local'));
            $url = OutboundUrl::join($bank->base_url, '/payout/'.$transaction->reference);
        } catch (InvalidArgumentException $e) {
            return new BankPayoutResult($transaction->status, $transaction->bank_reference, 'Bank API URL is not allowed.');
        }

        try {
            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders($this->headers($bank))
                ->get($url);

            $payload = $response->json() ?? [];
            $status = strtolower((string) ($payload['status'] ?? $transaction->status));

            return new BankPayoutResult(
                in_array($status, ['success', 'pending', 'failed'], true) ? $status : $transaction->status,
                $payload['bank_reference'] ?? $transaction->bank_reference,
                $payload['message'] ?? null,
                is_array($payload) ? $payload : [],
            );
        } catch (Throwable $e) {
            Log::warning('bank_status_error', ['bank_id' => $bank->id, 'error' => $e->getMessage()]);

            return new BankPayoutResult($transaction->status, $transaction->bank_reference, 'Bank request failed.');
        }
    }

    private function headers(Bank $bank): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

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
