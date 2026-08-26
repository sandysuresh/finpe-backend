<?php

namespace App\Services\Banking;

class BankPayoutResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $bankReference = null,
        public readonly ?string $message = null,
        public readonly array $raw = [],
    ) {}

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
