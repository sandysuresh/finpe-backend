<?php

namespace App\Services\Payout;

interface PayoutTransferProvider
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{status: string, provider_reference: ?string, provider_status_code: ?string, message: string, rrn?: ?string}
     */
    public function transfer(array $payload): array;
}
