<?php

namespace Tests\Support;

use App\Exceptions\PayoutException;
use App\Services\Payout\PayoutProvider;
use App\Support\CommissionProviders;

class FakePayoutTransfer implements PayoutProvider
{
    public function code(): string
    {
        return CommissionProviders::VIMOPAY;
    }

    public string $outcome = 'pending';

    /** @var array<string, mixed> */
    public array $payload = [];

    public function transfer(array $payload): array
    {
        $this->payload = $payload;

        if ($this->outcome === 'throw') {
            throw new PayoutException('Payout provider request failed.', 502);
        }

        return match ($this->outcome) {
            'success' => [
                'status' => 'success',
                'provider_reference' => 'PRV-100',
                'provider_status_code' => '000',
                'message' => 'Transaction successful.',
            ],
            'failed' => [
                'status' => 'failed',
                'provider_reference' => 'PRV-101',
                'provider_status_code' => '001',
                'message' => 'Transaction failed.',
            ],
            default => [
                'status' => 'pending',
                'provider_reference' => 'PRV-104',
                'provider_status_code' => '004',
                'message' => 'Transaction queued.',
            ],
        };
    }
}
