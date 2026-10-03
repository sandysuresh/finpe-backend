<?php

namespace App\Services;

use App\Exceptions\PayoutException;
use App\Models\Bank;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\VendorApiAccess;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Services\Banking\BankGatewayManager;
use App\Services\Payout\PayoutProviderRegistry;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PayoutService
{
    public function __construct(
        private BankGatewayManager $gateways,
        private VendorWebhook $webhooks,
        private PayoutProviderRegistry $providers,
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

        if ($transaction->status === 'success') {
            try {
                $transaction->forceFill([
                    'payout_provider' => $this->providers->current()->code(),
                ])->save();
                app(CommissionService::class)->recordForSuccessfulPayout($transaction->fresh());
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->webhooks->send($vendor, 'payout.'.$transaction->status, [
            'reference' => $transaction->reference,
            'bank_reference' => $transaction->bank_reference,
            'status' => $transaction->status,
            'amount' => (float) $transaction->amount,
            'service' => $transaction->service,
        ]);

        return $transaction;
    }

    public function transfer(Vendor $vendor, array $data): Transaction
    {
        $vendor->loadMissing('wallet');

        if ($vendor->status !== 'active') {
            throw new PayoutException('Vendor account is not active.');
        }

        if ($vendor->kyc_status !== 'verified') {
            throw new PayoutException('KYC must be approved before sending money.');
        }

        if (! $vendor->api_enabled || ! $vendor->hasEnabledApi(VendorApiAccess::PAYOUT)) {
            throw new PayoutException('API access is disabled for this vendor. Contact admin.', 403);
        }

        $mode = strtolower((string) $data['paymentMode']);
        if (! in_array($mode, ['imps', 'neft'], true)) {
            throw new PayoutException('Invalid payment mode. Use IMPS or NEFT.');
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount < 100 || $amount >= 100000) {
            throw new PayoutException('Amount must be at least 100 and below 100000.');
        }

        if ($vendor->transaction_limit && $amount > (float) $vendor->transaction_limit) {
            throw new PayoutException('Amount exceeds vendor transaction limit.');
        }

        $merchantRef = (string) $data['merchantRefId'];
        if (Transaction::query()->where('vendor_id', $vendor->id)->where('merchant_ref', $merchantRef)->exists()) {
            throw new PayoutException('This reference was already used.', 409);
        }

        $reference = 'TXN-'.strtoupper(Str::random(10));
        $provider = $this->providers->current();
        $charge = $this->payoutCharge($vendor, $amount, $provider->code());
        $total = round($amount + (float) $charge, 2);

        try {
            $transaction = DB::transaction(function () use ($vendor, $data, $mode, $amount, $charge, $total, $merchantRef, $reference, $provider) {
                $wallet = Wallet::query()
                    ->where('vendor_id', $vendor->id)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet) {
                    throw new PayoutException('Wallet not found.');
                }

                $balance = (float) $wallet->balance;
                if ($total > $balance) {
                    throw new PayoutException('Insufficient wallet balance. Available: ₹'.number_format($balance, 2));
                }

                $before = $balance;
                $after = round($before - $total, 2);
                $wallet->update(['balance' => $after]);

                $txn = Transaction::create([
                    'vendor_id' => $vendor->id,
                    'reference' => $reference,
                    'merchant_ref' => $merchantRef,
                    'amount' => $amount,
                    'payout_charge' => $charge,
                    'payout_provider' => $provider->code(),
                    'type' => 'payout',
                    'channel' => 'api',
                    'service' => $mode,
                    'beneficiary_name' => $data['beneficiaryName'],
                    'account_number' => $data['beneficiaryAccountNumber'],
                    'ifsc_code' => $data['beneficiaryIFSC'],
                    'beneficiary_bank_code' => $data['beneficiaryBank'],
                    'beneficiary_mobile' => $data['beneficiaryMobileNumber'],
                    'payment_purpose' => $data['paymentPurpose'],
                    'beneficiary_location' => $data['beneficiaryLocation'],
                    'status' => 'pending',
                ]);

                WalletLedger::create([
                    'vendor_id' => $vendor->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $total,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'reference' => $reference,
                    'description' => 'Payout '.$mode,
                    'source' => 'transaction',
                ]);

                $result = $provider->transfer([
                    'amount' => $amount,
                    'merchantRefId' => $merchantRef,
                    'beneficiaryBank' => $data['beneficiaryBank'],
                    'paymentPurpose' => $data['paymentPurpose'],
                    'paymentMode' => $mode,
                    'beneficiaryAccountNumber' => $data['beneficiaryAccountNumber'],
                    'beneficiaryIFSC' => $data['beneficiaryIFSC'],
                    'beneficiaryMobileNumber' => $data['beneficiaryMobileNumber'],
                    'beneficiaryName' => $data['beneficiaryName'],
                    'beneficiaryLocation' => $data['beneficiaryLocation'],
                    'lat' => $data['lat'],
                    'long' => $data['long'],
                    'udf1' => $data['udf1'] ?? '',
                    'udf2' => $data['udf2'] ?? '',
                    'udf3' => $data['udf3'] ?? '',
                ]);

                $update = [
                    'status' => $result['status'],
                    'bank_reference' => $result['provider_reference'],
                    'failure_reason' => $result['status'] === 'failed' ? $result['message'] : null,
                ];
                $rrn = $this->providerRrn($result['rrn'] ?? null);
                if ($rrn !== null && Schema::hasColumn('transactions', 'rrn')) {
                    $update['rrn'] = $rrn;
                }
                $txn->update($update);

                if ($result['status'] === 'failed') {
                    $this->refund($wallet, $vendor, $txn, $result['message'] ?: 'Payout failed');
                }

                return $txn->fresh();
            });
        } catch (QueryException $e) {
            if (in_array((string) ($e->errorInfo[0] ?? ''), ['23000', '23505'], true)) {
                throw new PayoutException('This reference was already used.', 409);
            }
            throw $e;
        }

        if ($transaction->status === 'success') {
            try {
                app(CommissionService::class)->recordForSuccessfulPayout($transaction);
            } catch (\Throwable $e) {
                report($e);
            }
        }

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

    public function applyProviderCallback(array $payload): Transaction
    {
        $code = (string) $payload['txnStatusCode'];
        $reported = strtolower(trim((string) $payload['txnStatus']));
        $status = match ($code) {
            '000' => $reported === 'success' ? 'success' : null,
            '001' => $reported === 'failed' ? 'failed' : null,
            '002' => in_array($reported, ['pending', 'inprogress', 'in progress'], true) ? 'pending' : null,
            '003' => in_array($reported, ['failed', 'validation failed'], true) ? 'failed' : null,
            '004' => in_array($reported, ['queued', 'pending'], true) ? 'pending' : null,
            default => null,
        };
        if ($status === null) {
            throw new PayoutException('Invalid callback.', 422);
        }

        if ($code === '000' && trim((string) ($payload['rrn'] ?? '')) === '') {
            throw new PayoutException('Invalid callback.', 422);
        }

        $message = $this->callbackMessage($payload['responseMessage'] ?? null);
        $txnId = trim((string) ($payload['txnId'] ?? ''));
        $merchantRef = trim((string) $payload['merchantRefId']);
        $recordCommission = false;

        $transaction = DB::transaction(function () use ($payload, $status, $message, $txnId, $merchantRef, &$recordCommission) {
            $txn = null;
            if ($txnId !== '') {
                $txn = Transaction::query()
                    ->where('type', 'payout')
                    ->where('bank_reference', $txnId)
                    ->lockForUpdate()
                    ->first();
            }
            if (! $txn) {
                $matches = Transaction::query()
                    ->where('type', 'payout')
                    ->where('merchant_ref', $merchantRef)
                    ->lockForUpdate()
                    ->get();
                if ($matches->count() !== 1) {
                    throw new PayoutException('Invalid callback.', 422);
                }
                $txn = $matches->first();
            }

            if ((string) $txn->merchant_ref !== $merchantRef) {
                throw new PayoutException('Invalid callback.', 422);
            }
            if ($txnId !== '' && $txn->bank_reference && (string) $txn->bank_reference !== $txnId) {
                throw new PayoutException('Invalid callback.', 422);
            }
            if (abs((float) $txn->amount - (float) $payload['amount']) > 0.001) {
                throw new PayoutException('Invalid callback.', 422);
            }

            $alreadyFailed = $txn->status === 'failed';
            $previousStatus = $txn->status;
            $nextStatus = $txn->status === 'pending' || $txn->status === $status ? $status : $txn->status;
            $recordCommission = $nextStatus === 'success' && $previousStatus !== 'success';
            $txn->update([
                'status' => $nextStatus,
                'bank_reference' => $txn->bank_reference ?: ($txnId !== '' ? $txnId : null),
                'failure_reason' => $message,
            ]);

            if ($nextStatus === 'failed' && ! $alreadyFailed) {
                $wallet = Wallet::query()->where('vendor_id', $txn->vendor_id)->lockForUpdate()->first();
                $vendor = $txn->vendor;
                if (! $wallet || ! $vendor) {
                    throw new PayoutException('Invalid callback.', 422);
                }
                $this->refund($wallet, $vendor, $txn, $message);
            }

            return $txn->fresh();
        });

        if ($recordCommission) {
            try {
                app(CommissionService::class)->recordForSuccessfulPayout($transaction);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $transaction;
    }

    private function providerRrn(mixed $value): ?string
    {
        if (! is_scalar($value) || is_bool($value)) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '' || strcasecmp($text, 'null') === 0 || strlen($text) > 40) {
            return null;
        }

        return $text;
    }

    private function payoutCharge(Vendor $vendor, float $amount, string $provider): string
    {
        $commission = app(CommissionService::class);
        $rule = $commission->select($commission->rulesForVendor((int) $vendor->id), [
            'vendor_id' => (int) $vendor->id,
            'merchant_id' => null,
            'provider' => $provider,
            'type' => 'payout',
            'at' => now(),
        ]);

        if (! $rule) {
            return '0.00';
        }

        return $commission->calculate(
            (string) $rule->calc_type,
            $commission->money($amount),
            $commission->money($rule->value),
        );
    }

    private function callbackMessage(mixed $message): string
    {
        $text = is_scalar($message) ? trim((string) $message) : '';
        if ($text === '' || preg_match('/https?:\/\/|vimopay/i', $text) === 1) {
            return 'Payout status received.';
        }

        return mb_substr($text, 0, 255);
    }

    private function refund(Wallet $wallet, Vendor $vendor, Transaction $txn, string $reason): void
    {
        $wallet->refresh();
        $before = (float) $wallet->balance;
        $amount = round((float) $txn->amount + (float) $txn->payout_charge, 2);
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
