<?php

namespace App\Services\Banking;

use App\Models\Bank;
use App\Models\Transaction;

class SimulatorBankGateway implements BankGateway
{
    public function testConnection(Bank $bank): BankPayoutResult
    {
        return new BankPayoutResult('success', 'SIM-OK', 'Sandbox simulator is ready.');
    }

    public function payout(Bank $bank, Transaction $transaction): BankPayoutResult
    {
        $account = (string) $transaction->account_number;

        if ($account !== '' && str_ends_with($account, '0000')) {
            return new BankPayoutResult('failed', null, 'Bank rejected the account number (simulator).');
        }

        if ((float) $transaction->amount >= 500000) {
            return new BankPayoutResult(
                'pending',
                'BNK-'.strtoupper(substr(md5($transaction->reference), 0, 10)),
                'Awaiting bank confirmation (simulator large-amount rule).',
            );
        }

        return new BankPayoutResult(
            'success',
            'BNK-'.strtoupper(substr(md5($transaction->reference), 0, 10)),
            'Payout accepted by sandbox bank.',
        );
    }

    public function status(Bank $bank, Transaction $transaction): BankPayoutResult
    {
        if ($transaction->status === 'pending' && (float) $transaction->amount >= 500000) {
            return new BankPayoutResult(
                'success',
                $transaction->bank_reference ?: 'BNK-SETTLED',
                'Simulator settled pending payout.',
            );
        }

        return new BankPayoutResult(
            $transaction->status,
            $transaction->bank_reference,
            'Current simulator status.',
        );
    }
}
