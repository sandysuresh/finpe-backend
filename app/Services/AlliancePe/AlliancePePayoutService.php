<?php

namespace App\Services\AlliancePe;

use App\Exceptions\AlliancePeException;
use App\Models\AlliancePePayout;
use App\Models\AlliancePePayoutEvent;
use App\Models\CommissionEntry;
use App\Models\Vendor;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Services\CommissionService;
use App\Support\CommissionProviders;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AlliancePePayoutService
{
    public const COMMISSION_SOURCE = 'alliancepe_payouts';

    public function __construct(private AlliancePeClient $client) {}

    /**
     * @param  array{amount: float|int|string, beneficiary_name: string, bank_name: string, account_number: string, ifsc: string, phone: string, email: string, transfer_mode: string, remarks?: string|null}  $input
     */
    public function submit(Vendor $vendor, array $input, ?string $merchantOrderId = null): AlliancePePayout
    {
        $merchantOrderId = $merchantOrderId ?: $this->merchantOrderId($vendor);
        $existing = AlliancePePayout::query()->where('merchant_order_id', $merchantOrderId)->first();
        if ($existing) {
            if ((int) $existing->vendor_id !== (int) $vendor->id) {
                throw new AlliancePeException('This payout reference belongs to another vendor.', 403);
            }
            if ($existing->status === 'failed' && $existing->wallet_state === 'not_debited') {
                throw new AlliancePeException($existing->failure_reason ?: 'Insufficient wallet balance.', 422, 'INSUFFICIENT_BALANCE');
            }
            if ($existing->provider_payout_id || $existing->http_status !== null) {
                return $existing;
            }
        }

        $amount = round((float) $input['amount'], 2);
        if ($amount < 100 || $amount > 100000) {
            throw new AlliancePeException('Amount must be from 100 to 100000.', 400, 'AMOUNT_OUT_OF_RANGE');
        }

        $payout = $existing ?: $this->debit($vendor, $input, $merchantOrderId, $amount);

        try {
            $result = $this->client->createPayout($this->payload($payout));
        } catch (AlliancePeException $e) {
            if ($e->statusCode === 429 || $e->errorCode === 'GATEWAY_UNAVAILABLE') {
                $payout->update([
                    'http_status' => $e->statusCode,
                    'provider_code' => $e->errorCode,
                    'failure_reason' => $e->getMessage(),
                    'status' => 'pending',
                ]);
                $this->recordTimeline($payout, 'provider');

                return $payout->fresh();
            }
            throw $e;
        }

        return $this->applyCreateResult($payout, $result['status'], $result['body']);
    }

    public function reconcile(AlliancePePayout $payout): AlliancePePayout
    {
        if (! $payout->provider_payout_id) {
            return $payout;
        }

        $result = $this->client->payout($payout->provider_payout_id);
        $data = is_array($result['body']['data'] ?? null) ? $result['body']['data'] : $result['body'];
        $this->applyProviderStatus($payout, is_array($data) ? $data : [], $result['status'], 'reconcile');

        return $payout->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyProviderStatus(AlliancePePayout $payout, array $data, ?int $httpStatus = null, string $source = 'provider'): void
    {
        $status = strtolower((string) ($data['status'] ?? ''));
        if (! in_array($status, ['pending', 'processing', 'completed', 'failed', 'cancelled', 'on_hold', 'rejected'], true)) {
            return;
        }

        DB::transaction(function () use ($payout, $data, $httpStatus, $source, $status) {
            $current = AlliancePePayout::query()->whereKey($payout->id)->lockForUpdate()->first();
            if (! $current) {
                return;
            }

            $utr = $data['utrNumber'] ?? $data['utrId'] ?? null;
            $providerReference = $this->text($data['providerPayoutId'] ?? $data['payoutId'] ?? $data['id'] ?? null);
            $current->update([
                'status' => $status,
                'provider_payout_id' => $current->provider_payout_id ?: $providerReference,
                'utr_number' => is_scalar($utr) ? (string) $utr : $current->utr_number,
                'failure_reason' => $this->text($data['failureReason'] ?? null) ?: $current->failure_reason,
                'http_status' => $httpStatus ?: $current->http_status,
            ]);

            if (in_array($status, ['failed', 'cancelled'], true)) {
                $this->refundOnce($current->fresh(), $status);
            }
            if ($status === 'completed') {
                $this->recordCommissionSnapshot($current->fresh());
            }
            $this->recordTimeline($current, $source);
        });
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function applyCreateResult(AlliancePePayout $payout, int $status, array $body): AlliancePePayout
    {
        $code = $this->text($body['code'] ?? ($body['error']['code'] ?? null));
        $message = $this->text($body['message'] ?? ($body['error']['message'] ?? null)) ?: 'AlliancePe request failed.';
        $data = is_array($body['data'] ?? null) ? $body['data'] : [];

        if ($status === 201 || $status === 200) {
            $providerStatus = strtolower((string) ($data['status'] ?? 'pending'));
            $payout->update([
                'http_status' => $status,
                'provider_code' => null,
                'provider_payout_id' => $this->text($data['payoutId'] ?? $data['id'] ?? null),
                'status' => in_array($providerStatus, ['pending', 'processing', 'completed', 'failed', 'cancelled', 'on_hold'], true) ? $providerStatus : 'pending',
                'utr_number' => $this->text($data['utrNumber'] ?? $data['utrId'] ?? null),
                'failure_reason' => null,
            ]);
            if (in_array($payout->status, ['failed', 'cancelled'], true)) {
                $this->refundOnce($payout->fresh(), $payout->status);
            }
            if ($payout->status === 'completed') {
                $this->recordCommissionSnapshot($payout->fresh());
            }
            $this->recordTimeline($payout, 'provider');

            return $payout->fresh();
        }

        if ($status === 409) {
            $payout->update([
                'http_status' => 409,
                'provider_code' => 'DUPLICATE',
                'provider_payout_id' => $payout->provider_payout_id ?: $this->text($data['payoutId'] ?? $data['id'] ?? null),
                'failure_reason' => $message,
            ]);
            $this->recordTimeline($payout, 'provider');

            return $payout->fresh();
        }

        if ($status === 400 && $code === 'AMOUNT_OUT_OF_RANGE') {
            $this->refundOnce($payout, 'amount');
            $payout->update(['http_status' => 400, 'provider_code' => $code, 'status' => 'failed', 'failure_reason' => $message]);
            $this->recordTimeline($payout, 'provider');

            throw new AlliancePeException($message, 400, $code);
        }

        if ($status === 400 && $code === 'INVALID_BANK_DETAILS') {
            $this->refundOnce($payout, 'bank');
            $payout->update(['http_status' => 400, 'provider_code' => $code, 'status' => 'failed', 'failure_reason' => $message]);
            $this->recordTimeline($payout, 'provider');

            throw new AlliancePeException($message, 400, $code);
        }

        if ($status === 502) {
            $this->refundOnce($payout, 'rejected');
            $payout->update(['http_status' => 502, 'provider_code' => $code ?: 'GATEWAY_REJECTED', 'status' => 'failed', 'failure_reason' => $message]);
            $this->recordTimeline($payout, 'provider');

            return $payout->fresh();
        }

        if ($status === 503) {
            $payout->update(['http_status' => 503, 'provider_code' => $code ?: 'GATEWAY_UNAVAILABLE', 'status' => 'pending', 'failure_reason' => $message]);
            $this->recordTimeline($payout, 'provider');

            return $payout->fresh();
        }

        if ($status === 400) {
            $this->refundOnce($payout, 'rejected');
            $payout->update(['http_status' => 400, 'provider_code' => $code, 'status' => 'failed', 'failure_reason' => $message]);
            $this->recordTimeline($payout, 'provider');

            throw new AlliancePeException($message, 400, $code);
        }

        Log::warning('alliancepe_payout_unhandled', ['http_status' => $status, 'code' => $code]);
        $payout->update(['http_status' => $status, 'provider_code' => $code, 'failure_reason' => $message]);
        $this->recordTimeline($payout, 'provider');

        throw new AlliancePeException($message, $status >= 400 ? $status : 502, $code);
    }

    /**
     * @param  array{beneficiary_name: string, bank_name: string, account_number: string, ifsc: string, phone: string, email: string, transfer_mode: string, remarks?: string|null}  $input
     */
    private function debit(Vendor $vendor, array $input, string $merchantOrderId, float $amount): AlliancePePayout
    {
        $snapshot = $this->commissionSnapshot($vendor, $amount);
        $declined = false;

        $payout = DB::transaction(function () use ($vendor, $input, $merchantOrderId, $amount, $snapshot, &$declined) {
            $wallet = Wallet::query()->where('vendor_id', $vendor->id)->lockForUpdate()->first();
            if (! $wallet) {
                throw new AlliancePeException('Wallet not found.', 422);
            }
            $total = round((float) $snapshot['total_debited'], 2);
            $before = round((float) $wallet->balance, 2);
            if ($total > $before) {
                $declined = true;

                return $this->storeInsufficientAttempt($vendor, $input, $merchantOrderId, $amount, $snapshot);
            }
            $after = round($before - $total, 2);
            $wallet->update(['balance' => $after]);
            $payout = AlliancePePayout::query()->create([
                'vendor_id' => $vendor->id,
                'environment' => $this->client->environment(),
                'merchant_order_id' => $merchantOrderId,
                'amount' => $amount,
                'commission_rule_id' => $snapshot['commission_rule_id'],
                'commission_type' => $snapshot['commission_type'],
                'commission_rate' => $snapshot['commission_rate'],
                'commission_amount' => $snapshot['commission_amount'],
                'total_debited' => $snapshot['total_debited'],
                'status' => 'pending',
                'wallet_state' => 'debited',
                'beneficiary_name' => $input['beneficiary_name'],
                'bank_name' => $input['bank_name'],
                'account_number' => $input['account_number'],
                'ifsc_code' => $input['ifsc'],
                'phone' => $input['phone'],
                'email' => $input['email'],
                'transfer_mode' => $input['transfer_mode'],
                'remarks' => $input['remarks'] ?? null,
            ]);
            WalletLedger::query()->create([
                'vendor_id' => $vendor->id,
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $snapshot['total_debited'],
                'balance_before' => $before,
                'balance_after' => $after,
                'reference' => $merchantOrderId,
                'description' => 'AlliancePe payout',
                'source' => 'alliancepe',
            ]);
            $this->recordTimeline($payout, 'created');

            return $payout;
        });

        if ($declined) {
            throw new AlliancePeException('Insufficient wallet balance.', 422, 'INSUFFICIENT_BALANCE');
        }

        return $payout;
    }

    /**
     * @param  array{beneficiary_name: string, bank_name: string, account_number: string, ifsc: string, phone: string, email: string, transfer_mode: string, remarks?: string|null}  $input
     * @param  array{commission_rule_id: int|null, commission_type: string|null, commission_rate: string|null, commission_amount: string, total_debited: string}  $snapshot
     */
    private function storeInsufficientAttempt(Vendor $vendor, array $input, string $merchantOrderId, float $amount, array $snapshot): AlliancePePayout
    {
        $payout = AlliancePePayout::query()->create([
            'vendor_id' => $vendor->id,
            'environment' => $this->client->environment(),
            'merchant_order_id' => $merchantOrderId,
            'amount' => $amount,
            'commission_rule_id' => $snapshot['commission_rule_id'],
            'commission_type' => $snapshot['commission_type'],
            'commission_rate' => $snapshot['commission_rate'],
            'commission_amount' => $snapshot['commission_amount'],
            'total_debited' => '0.00',
            'status' => 'failed',
            'wallet_state' => 'not_debited',
            'beneficiary_name' => $input['beneficiary_name'],
            'bank_name' => $input['bank_name'],
            'account_number' => $input['account_number'],
            'ifsc_code' => $input['ifsc'],
            'phone' => $input['phone'],
            'email' => $input['email'],
            'transfer_mode' => $input['transfer_mode'],
            'remarks' => $input['remarks'] ?? null,
            'failure_reason' => 'Insufficient wallet balance.',
            'http_status' => 422,
            'provider_code' => 'INSUFFICIENT_BALANCE',
        ]);
        $this->recordTimeline($payout, 'created');

        return $payout;
    }

    private function recordTimeline(AlliancePePayout $payout, string $source): void
    {
        $current = $payout->fresh();
        if (! $current) {
            return;
        }
        $latest = AlliancePePayoutEvent::query()->where('alliancepe_payout_id', $current->id)->latest('id')->first();
        if ($latest
            && $latest->status === $current->status
            && $latest->wallet_state === $current->wallet_state
            && $latest->utr_number === $current->utr_number
            && $latest->failure_reason === $current->failure_reason) {
            return;
        }

        AlliancePePayoutEvent::query()->create([
            'alliancepe_payout_id' => $current->id,
            'status' => $current->status,
            'wallet_state' => $current->wallet_state,
            'utr_number' => $current->utr_number,
            'failure_reason' => $current->failure_reason,
            'source' => $source,
        ]);
    }

    private function refundOnce(AlliancePePayout $payout, string $reason): void
    {
        DB::transaction(function () use ($payout, $reason) {
            $locked = AlliancePePayout::query()->whereKey($payout->id)->lockForUpdate()->first();
            if (! $locked || $locked->wallet_state !== 'debited') {
                return;
            }
            $wallet = Wallet::query()->where('vendor_id', $locked->vendor_id)->lockForUpdate()->first();
            if (! $wallet) {
                return;
            }
            $credit = round((float) ($locked->total_debited ?? $locked->amount), 2);
            $before = round((float) $wallet->balance, 2);
            $after = round($before + $credit, 2);
            $wallet->update(['balance' => $after]);
            WalletLedger::query()->create([
                'vendor_id' => $locked->vendor_id,
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $credit,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference' => $locked->merchant_order_id,
                'description' => 'AlliancePe payout refund: '.$reason,
                'source' => 'alliancepe',
            ]);
            $locked->update(['wallet_state' => 'refunded', 'refunded_at' => now()]);
        });
    }

    /**
     * @return array{commission_rule_id: int|null, commission_type: string|null, commission_rate: string|null, commission_amount: string, total_debited: string}
     */
    private function commissionSnapshot(Vendor $vendor, float $amount): array
    {
        $commission = app(CommissionService::class);
        $rule = $commission->select($commission->rulesForVendor((int) $vendor->id), [
            'vendor_id' => (int) $vendor->id,
            'merchant_id' => null,
            'provider' => CommissionProviders::ALLIANCEPE,
            'type' => 'payout',
            'at' => now(),
        ]);
        $base = $commission->money($amount);
        if (! $rule) {
            return [
                'commission_rule_id' => null,
                'commission_type' => null,
                'commission_rate' => null,
                'commission_amount' => '0.00',
                'total_debited' => $base,
            ];
        }

        $rate = $commission->money($rule->value);
        $charge = $commission->calculate((string) $rule->calc_type, $base, $rate);

        return [
            'commission_rule_id' => $rule->id,
            'commission_type' => (string) $rule->calc_type,
            'commission_rate' => $rate,
            'commission_amount' => $charge,
            'total_debited' => bcadd($base, $charge, 2),
        ];
    }

    private function recordCommissionSnapshot(?AlliancePePayout $payout): void
    {
        if (! $payout || $payout->status !== 'completed' || $payout->commission_rule_id === null) {
            return;
        }

        $existing = CommissionEntry::query()
            ->where('source_type', self::COMMISSION_SOURCE)
            ->where('source_id', $payout->id)
            ->first();
        if ($existing) {
            return;
        }

        $payout->loadMissing('vendor');
        try {
            CommissionEntry::query()->create([
                'commission_rule_id' => $payout->commission_rule_id,
                'vendor_id' => $payout->vendor_id,
                'vendor_name_snapshot' => $payout->vendor?->business_name,
                'merchant_id' => null,
                'merchant_code_snapshot' => null,
                'service_snapshot' => $payout->transfer_mode,
                'type_snapshot' => 'payout',
                'source_type' => self::COMMISSION_SOURCE,
                'source_id' => $payout->id,
                'source_reference' => $payout->merchant_order_id,
                'source_status_snapshot' => 'completed',
                'base_amount' => $payout->amount,
                'calc_type' => $payout->commission_type,
                'rate_value' => $payout->commission_rate,
                'commission_amount' => $payout->commission_amount,
                'status' => CommissionEntry::STATUS_RECORDED,
            ]);
        } catch (QueryException $e) {
            $state = $e->errorInfo[0] ?? '';
            if (! in_array($state, ['23000', '23505'], true)) {
                throw $e;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AlliancePePayout $payout): array
    {
        $body = [
            'amount' => (float) $payout->amount,
            'beneficiaryName' => $payout->beneficiary_name,
            'beneficiaryAccountNumber' => $payout->account_number,
            'beneficiaryBankName' => $payout->bank_name,
            'beneficiaryIfscCode' => $payout->ifsc_code,
            'beneficiaryPhoneNumber' => $payout->phone,
            'beneficiaryEmail' => $payout->email,
            'merchantOrderId' => $payout->merchant_order_id,
            'transferMode' => $payout->transfer_mode,
        ];
        if ($payout->remarks) {
            $body['remarks'] = $payout->remarks;
        }
        $callback = \App\Support\AlliancePeCallback::url();
        if ($callback !== null) {
            $body['callbackUrl'] = $callback;
        }

        return $body;
    }

    private function merchantOrderId(Vendor $vendor): string
    {
        return 'AP-'.$vendor->id.'-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6));
    }

    private function text(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }
        $text = trim((string) $value);

        return $text === '' ? null : mb_substr($text, 0, 255);
    }
}
