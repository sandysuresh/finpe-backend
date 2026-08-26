<?php

namespace App\Services\Banking;

use App\Models\Bank;
use App\Models\Transaction;

interface BankGateway
{
    public function testConnection(Bank $bank): BankPayoutResult;

    public function payout(Bank $bank, Transaction $transaction): BankPayoutResult;

    public function status(Bank $bank, Transaction $transaction): BankPayoutResult;
}
