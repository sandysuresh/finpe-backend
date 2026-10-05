<?php

namespace App\Services\Payout;

interface PayoutProvider extends PayoutTransferProvider
{
    public function code(): string;

    public function name(): string;

    public function environment(): string;
}
