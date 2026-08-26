<?php

namespace App\Services;

use App\Exceptions\PayoutException;
use App\Models\Bank;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Services\Banking\BankGatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayoutService
{
    public function __construct(
        private BankGatewayManager $gateways,
        private VendorWebhook $webhooks,
    ) {}

    public function send(Vendor $vendor, array $data, string $channel = 'panel'): Transaction
    {
        $vendor->loadMissing(['wallet', 'apiCredential']);

        if ($vendor->status !== 'active') {
            throw new PayoutException('Vendor account is not active.');
        }

        if ($vendor->kyc_status !== 'verified') {
            throw new PayoutException('KYC must be approved before sending money.');
        }

        if ($channel === 'api' && ! $vendor->api_enabled) {
            throw new PayoutException('API access is disabled for this vendor. Contact admin.', 403);
        }

        if ($vendor->assignedBanks()->doesntExist()) {
            throw new PayoutException('No bank API is assigned to this vendor. Contact admin.', 403);
        }

        $service = strtolower((string) ($data['service'] ?? 'imps'));
        if (! in_array($service, ['imps', 'neft', 'rtgs'], true)) {
            throw new PayoutException('Invalid service. Use IMPS, NEFT or RTGS.');
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            throw new PayoutException('Amount must be greater than zero.');
        }

        if ($vendor->transaction_limit && $amount > (float) $vendor->transaction_limit) {
            throw new PayoutException('Amount exceeds vendor transaction limit.');
        }

        $bank = $this->resolveBank($vendor, $data['bank_code'] ?? null);
        if (! $bank) {
            throw new PayoutException('No bank API is assigned to this vendor. Contact admin.', 403);
        }

        if (! $bank->supports($service)) {
            throw new PayoutException('Selected bank does not support '.$service.'.');
        }

        $reference = 'TXN-'.strtoupper(Str::random(10));

        $transaction = DB::transaction(function () use ($vendor, $data, $channel, $service, $amount, $bank, $reference) {
            $wallet = Wallet::query()
                ->where('vendor_id', $vendor->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw new PayoutException('Wallet not found.');
            }

            $balance = (float) $wallet->balance;
            if ($amount > $balance) {
                throw new PayoutException('Insufficient wallet balance. Available: ₹'.number_format($balance, 2));
            }

            $before = $balance;
            $after = round($before - $amount, 2);
            $wallet->update(['balance' => $after]);

            $txn = Transaction::create([
                'vendor_id' => $vendor->id,
                'bank_id' => $bank->id,
                'reference' => $reference,
                'amount' => $amount,
                'type' => 'payout',
                'channel' => $channel,
                'service' => $service,
                'beneficiary_name' => $data['beneficiary_name'],
                'account_number' => $data['account_number'] ?? null,
                'ifsc_code' => $data['ifsc_code'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'pending',
            ]);

            WalletLedger::create([
                'vendor_id' => $vendor->id,
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference' => $reference,
                'description' => 'Payout '.$service,
                'source' => 'transaction',
            ]);

            $result = $this->gateways->for($bank)->payout($bank, $txn);

            $txn->update([
                'status' => $result->status,
                'bank_reference' => $result->bankReference,
                'failure_reason' => $result->isFailed() ? $result->message : null,
            ]);

            if ($result->isFailed()) {
                $this->refund($wallet, $vendor, $txn, $result->message ?: 'Bank declined payout');
            }

            return $txn->fresh();
        });

        $this->webhooks->send($vendor, 'payout.'.$transaction->status, [
            'reference' => $transaction->reference,
            'bank_reference' => $transaction->bank_reference,
            'status' => $transaction->status,
            'amount' => (float) $transaction->amount,
            'service' => $transaction->service,
        ]);

        return $transaction;
    }

    private function resolveBank(Vendor $vendor, ?string $bankCode): ?Bank
    {
        $query = $vendor->assignedBanks();

        if ($bankCode) {
            return $query->where('banks.code', strtoupper($bankCode))->first();
        }

        return $query->orderByDesc('vendor_banks.id')->first();
    }

    private function refund(Wallet $wallet, Vendor $vendor, Transaction $txn, string $reason): void
    {
        $wallet->refresh();
        $before = (float) $wallet->balance;
        $amount = (float) $txn->amount;
        $after = round($before + $amount, 2);
        $wallet->update(['balance' => $after]);

        WalletLedger::create([
            'vendor_id' => $vendor->id,
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'reference' => $txn->reference,
            'description' => 'Payout refund: '.$reason,
            'source' => 'transaction',
        ]);
    }
}
