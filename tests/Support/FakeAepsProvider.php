<?php

namespace Tests\Support;

use App\Exceptions\AepsException;
use App\Services\Aeps\AepsProvider;

class FakeAepsProvider implements AepsProvider
{
    public array $calls = [];

    public array $result = [];

    public ?AepsException $failure = null;

    public function banks(): array
    {
        return $this->hit('banks');
    }

    public function bankIin(string $txnCode, string $authType): array
    {
        return $this->hit('bankIin', [$txnCode, $authType]);
    }

    public function states(): array
    {
        return $this->hit('states');
    }

    public function districts(string $stateCode): array
    {
        return $this->hit('districts', [$stateCode]);
    }

    public function registerMerchant(array $payload): array
    {
        return $this->hit('registerMerchant', [$payload]);
    }

    public function sendOtp(array $payload): array
    {
        return $this->hit('sendOtp', [$payload]);
    }

    public function resendOtp(array $payload): array
    {
        return $this->hit('resendOtp', [$payload]);
    }

    public function verifyOtp(array $payload): array
    {
        return $this->hit('verifyOtp', [$payload]);
    }

    public function ekyc(array $payload): array
    {
        return $this->hit('ekyc', [$payload]);
    }

    public function twoFactor(array $payload): array
    {
        return $this->hit('twoFactor', [$payload]);
    }

    public function transactionOtp(array $payload): array
    {
        return $this->hit('transactionOtp', [$payload]);
    }

    public function transact(array $payload): array
    {
        return $this->hit('transact', [$payload]);
    }

    public function callsTo(string $method): int
    {
        return count(array_filter($this->calls, fn (array $call) => $call['method'] === $method));
    }

    private function hit(string $method, array $args = []): array
    {
        $this->calls[] = ['method' => $method, 'args' => $args];
        if ($this->failure) {
            throw $this->failure;
        }

        return $this->result;
    }
}
