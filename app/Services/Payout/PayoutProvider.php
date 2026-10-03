<?php

namespace App\Services\Payout;

interface PayoutProvider extends PayoutTransferProvider
{
    public function code(): string;
}
