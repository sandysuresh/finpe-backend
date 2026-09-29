<?php

namespace App\Services\Aeps;

interface AepsProvider
{
    public function banks(): array;

    public function bankIin(string $txnCode, string $authType): array;

    public function states(): array;

    public function districts(string $stateCode): array;

    public function registerMerchant(array $payload): array;

    public function sendOtp(array $payload): array;

    public function resendOtp(array $payload): array;

    public function verifyOtp(array $payload): array;

    public function ekyc(array $payload): array;

    public function twoFactor(array $payload): array;

    public function transactionOtp(array $payload): array;

    public function transact(array $payload): array;
}
