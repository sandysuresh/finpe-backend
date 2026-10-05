<?php

namespace App\Services\Payout;

use App\Exceptions\AepsException;
use App\Exceptions\PayoutException;
use App\Services\Aeps\VimoPayClient;
use App\Support\CommissionProviders;

class VimoPayPayoutTransfer implements PayoutProvider
{
    public function code(): string
    {
        return CommissionProviders::VIMOPAY;
    }

    public function name(): string
    {
        return 'VimoPay';
    }

    public function environment(): string
    {
        $environment = strtoupper(trim((string) config('services.vimopay.environment', 'uat')));

        return $environment !== '' ? $environment : 'UAT';
    }

    public function __construct(private VimoPayClient $client) {}

    public function transfer(array $payload): array
    {
        try {
            $envelope = $this->client->post($this->client->path('payout_transfer'), $payload);
        } catch (AepsException) {
            throw new PayoutException('Payout provider request failed.', 502);
        }

        $record = is_array($envelope['data'] ?? null) && array_is_list($envelope['data']) === false
            ? $envelope['data']
            : [];
        $code = $this->statusCode($record, $envelope);
        $status = $this->status($code);

        if ($status === null) {
            throw new PayoutException($this->safeMessage($envelope['message'] ?? $record['responseMessage'] ?? null), 502);
        }

        return [
            'status' => $status,
            'provider_reference' => $this->text($record['txnId'] ?? null),
            'provider_status_code' => $code,
            'message' => $this->safeMessage($record['responseMessage'] ?? $envelope['message'] ?? null),
            'rrn' => $this->rrn($record['rrn'] ?? null),
        ];
    }

    private function statusCode(array $record, array $envelope): ?string
    {
        foreach ([$record['txnStatusCode'] ?? null, $envelope['responseCode'] ?? null] as $candidate) {
            if (is_scalar($candidate) && preg_match('/^\d{3}$/', (string) $candidate) === 1) {
                return (string) $candidate;
            }
        }

        return null;
    }

    private function status(?string $code): ?string
    {
        return match ($code) {
            '000' => 'success',
            '001', '003' => 'failed',
            '002', '004' => 'pending',
            default => null,
        };
    }

    private function rrn(mixed $value): ?string
    {
        $text = $this->text($value);
        if ($text === null || strcasecmp($text, 'null') === 0 || strlen($text) > 40) {
            return null;
        }

        return $text;
    }

    private function text(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function safeMessage(mixed $message): string
    {
        if (! is_string($message) || $message === '' || preg_match('/https?:\/\/|vimopay/i', $message) === 1) {
            return 'Payout provider request failed.';
        }

        return $message;
    }
}
