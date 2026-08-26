<?php

namespace App\Services\Banking;

use App\Models\Bank;
use InvalidArgumentException;

class BankGatewayManager
{
    public function for(Bank $bank): BankGateway
    {
        return match ($bank->driver) {
            'http' => app(HttpBankGateway::class),
            'simulator' => app(SimulatorBankGateway::class),
            default => throw new InvalidArgumentException('Unsupported bank driver: '.$bank->driver),
        };
    }
}
