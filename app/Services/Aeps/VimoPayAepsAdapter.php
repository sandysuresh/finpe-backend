<?php

namespace App\Services\Aeps;

class VimoPayAepsAdapter implements AepsProvider
{
    public function __construct(private VimoPayClient $client) {}

    public function banks(): array
    {
        return $this->normalize($this->client->get($this->client->path('banks')));
    }

    public function bankIin(string $txnCode, string $authType): array
    {
        return $this->normalize($this->client->post($this->client->path('bank_iin'), [
            'txnCode' => $txnCode,
            'authType' => $authType,
        ]));
    }

    public function states(): array
    {
        return $this->normalize($this->client->get($this->client->path('states')));
    }

    public function districts(string $stateCode): array
    {
        return $this->normalize($this->client->post($this->client->path('districts'), [
            'stateCode' => $stateCode,
        ]));
    }

    public function registerMerchant(array $payload): array
    {
        return $this->normalize($this->client->post($this->client->path('register'), $payload));
    }

    public function sendOtp(array $payload): array
    {
        return $this->normalize($this->client->post($this->client->path('send_otp'), $payload));
    }

    public function resendOtp(array $payload): array
    {
        return $this->normalize($this->client->post($this->client->path('resend_otp'), $payload));
    }

    public function verifyOtp(array $payload): array
    {
        return $this->normalize($this->client->post($this->client->path('verify_otp'), $payload));
    }

    public function ekyc(array $payload): array
    {
        return $this->normalize($this->client->post($this->client->path('ekyc'), $payload));
    }

    public function twoFactor(array $payload): array
    {
        return $this->normalize($this->client->post($this->client->path('two_factor'), $payload));
    }

    public function transactionOtp(array $payload): array
    {
        return $this->normalize($this->client->post($this->client->path('transaction_otp'), $payload));
    }

    public function transact(array $payload): array
    {
        return $this->normalize($this->client->post($this->client->path('transact'), $payload));
    }

    private function normalize(array $envelope): array
    {
        $data = $envelope['data'] ?? [];
        $record = is_array($data) && array_is_list($data) === false ? $data : [];
        if (isset($record['data']) && is_array($record['data']) && array_is_list($record['data']) === false && isset($record['status']) === false) {
            $record = $record['data'];
        }

        $code = $this->statusCode($envelope, $record);
        $list = is_array($data) && array_is_list($data) ? $data : ($record['data'] ?? null);
        if (! is_array($list) || array_is_list($list) === false) {
            $list = is_array($data) && array_is_list($data) ? $data : null;
        }

        return [
            'provider_status_code' => $code,
            'status' => AepsStatus::fromProviderCode($code),
            'message' => is_string($envelope['message'] ?? null) ? $envelope['message'] : null,
            'provider_status_description' => $this->stringValue($record, 'statusDescription'),
            'provider_merchant_status' => $this->stringValue($record, 'merchantStatus'),
            'provider_merchant_id' => $this->stringValue($record, 'merchantId'),
            'provider_txn_ref' => $this->stringValue($record, 'txnRefId'),
            'merchant_ref' => $this->stringValue($record, 'merchantRefId'),
            'rrn' => $this->stringValue($record, 'rrn'),
            'npci_code' => $this->stringValue($record, 'npciCode'),
            'npci_message' => $this->stringValue($record, 'npciMessage'),
            'transaction_amount' => $this->stringValue($record, 'transactionAmount'),
            'provider_available_balance' => $this->stringValue($record, 'availableBalance'),
            'transaction_list' => $this->stringValue($record, 'transactionList'),
            'aadhaar_masked' => isset($record['aadhaarNo']) && is_string($record['aadhaarNo'])
                ? AepsPayloadSanitizer::maskDigits($record['aadhaarNo'])
                : null,
            'list' => $list,
            'sanitized' => AepsPayloadSanitizer::redact(is_array($data) ? $data : ['value' => $data]),
        ];
    }

    private function statusCode(array $envelope, array $record): ?string
    {
        $candidates = [
            $record['status'] ?? null,
            $envelope['responseCode'] ?? null,
        ];
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && preg_match('/^\d{3}$/', $candidate) === 1) {
                return $candidate;
            }
        }

        return null;
    }

    private function stringValue(array $record, string $key): ?string
    {
        $value = $record[$key] ?? null;
        if ($value === null || is_array($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
