<?php

namespace App\Services;

use App\Models\CommissionEntry;
use App\Models\CommissionRule;
use App\Models\Transaction;
use App\Support\CommissionProviders;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

/**
 * Payout commission only. AePS is out of scope.
 *
 * Rule selection for one source:
 * 1. status = active
 * 2. effective_from/effective_to contain the event time when those dates are set
 * 3. vendor_id is null (any vendor) or equals the source vendor
 * 4. provider equals the source provider exactly; a blank provider does not match a named provider
 * 5. type is null or equals the source type
 * 6. merchant_id is null, or equals the source merchant
 *    Payout transactions have no merchant_id, so a merchant-specific rule never matches a payout.
 * 7. One winner only:
 *    - merchant-specific rule (merchant_id set) outranks a vendor-only rule
 *    - a vendor-specific rule outranks a global rule (vendor_id null)
 *    - then higher priority (null priority is 0)
 *    - then higher rule id
 * A source without a merchant never produces two entries.
 *
 * Reversal, refund, and chargeback accounting is BUSINESS_RULE_PENDING.
 * This service never updates an existing snapshot and never writes the wallet.
 */
class CommissionService
{
    public const SOURCE_TRANSACTIONS = 'transactions';

    public function recordForSuccessfulPayout(Transaction $transaction): ?CommissionEntry
    {
        if ($transaction->status !== 'success') {
            return null;
        }

        $existing = $this->existingEntry(self::SOURCE_TRANSACTIONS, (int) $transaction->id);
        if ($existing) {
            return $existing;
        }

        $transaction->loadMissing('vendor');
        $at = $transaction->updated_at ?? $transaction->created_at ?? now();
        $provider = strtolower(trim((string) $transaction->payout_provider));
        if ($provider === '') {
            return null;
        }

        $context = [
            'vendor_id' => (int) $transaction->vendor_id,
            'merchant_id' => null,
            'provider' => $provider,
            'type' => 'payout',
            'at' => $at,
        ];

        $rule = $this->select($this->loadCandidates((int) $transaction->vendor_id), $context);
        if (! $rule) {
            return null;
        }

        $base = $this->money($transaction->amount);

        try {
            return CommissionEntry::query()->create([
                'commission_rule_id' => $rule->id,
                'vendor_id' => $transaction->vendor_id,
                'vendor_name_snapshot' => $transaction->vendor?->business_name,
                'merchant_id' => null,
                'merchant_code_snapshot' => null,
                'service_snapshot' => $transaction->service,
                'type_snapshot' => $transaction->type,
                'source_type' => self::SOURCE_TRANSACTIONS,
                'source_id' => $transaction->id,
                'source_reference' => $transaction->reference,
                'source_status_snapshot' => $transaction->status,
                'base_amount' => $base,
                'calc_type' => $rule->calc_type,
                'rate_value' => $this->money($rule->value),
                'commission_amount' => $this->calculate((string) $rule->calc_type, $base, $this->money($rule->value)),
                'status' => CommissionEntry::STATUS_RECORDED,
            ]);
        } catch (QueryException $e) {
            if ($this->isDuplicate($e)) {
                return $this->existingEntry(self::SOURCE_TRANSACTIONS, (int) $transaction->id);
            }

            throw $e;
        }
    }

    /**
     * @param  iterable<CommissionRule>  $rules
     * @param  array{vendor_id:int,merchant_id:?int,service:?string,type:?string,at:CarbonInterface}  $context
     */
    /**
     * The VimoPay payout rule for this vendor. Ranking is select().
     *
     * @param  iterable<CommissionRule>|null  $rules
     */
    public function configuredPayoutRule(int $vendorId, ?CarbonInterface $at = null, ?iterable $rules = null): ?CommissionRule
    {
        return $this->select($rules ?? $this->loadCandidates($vendorId), [
            'vendor_id' => $vendorId,
            'merchant_id' => null,
            'provider' => CommissionProviders::VIMOPAY,
            'type' => 'payout',
            'at' => $at ?? now(),
        ]);
    }

    public function selectAepsRule(int $vendorId, string $provider, ?int $merchantId = null, ?CarbonInterface $at = null): ?CommissionRule
    {
        return $this->select($this->loadCandidates($vendorId), [
            'vendor_id' => $vendorId,
            'merchant_id' => $merchantId,
            'provider' => $provider,
            'type' => 'aeps',
            'at' => $at ?? now(),
        ]);
    }

    public function select(iterable $rules, array $context): ?CommissionRule
    {
        $matches = Collection::make($rules)
            ->filter(fn (CommissionRule $rule) => $this->matches($rule, $context))
            ->sort(function (CommissionRule $a, CommissionRule $b) {
                $scope = $this->scopeRank($b) <=> $this->scopeRank($a);
                if ($scope !== 0) {
                    return $scope;
                }

                $priority = ((int) ($b->priority ?? 0)) <=> ((int) ($a->priority ?? 0));
                if ($priority !== 0) {
                    return $priority;
                }

                return ((int) $b->id) <=> ((int) $a->id);
            })
            ->values();

        return $matches->first();
    }

    public function calculate(string $calcType, string $baseAmount, string $value): string
    {
        if ($calcType === 'fixed') {
            return bcadd($value, '0', 2);
        }

        return bcdiv(bcmul($baseAmount, $value, 4), '100', 2);
    }

    public function money(mixed $amount): string
    {
        return bcadd((string) $amount, '0', 2);
    }

    /**
     * @param  array{vendor_id:int,merchant_id:?int,service:?string,type:?string,at:CarbonInterface}  $context
     */
    public function matches(CommissionRule $rule, array $context): bool
    {
        if ($rule->status !== 'active') {
            return false;
        }

        $at = $context['at'];
        if ($rule->effective_from && $at->lt($rule->effective_from)) {
            return false;
        }
        // effective_to is the last calendar day the rule applies. A time chosen
        // in the date picker must not expire the rule earlier that same day.
        if ($rule->effective_to && $at->gt($rule->effective_to->copy()->endOfDay())) {
            return false;
        }

        if ($rule->vendor_id !== null && (int) $rule->vendor_id !== (int) $context['vendor_id']) {
            return false;
        }

        if ($rule->merchant_id !== null && (int) $rule->merchant_id !== (int) ($context['merchant_id'] ?? 0)) {
            return false;
        }

        $contextProvider = strtolower(trim((string) ($context['provider'] ?? '')));
        $ruleProvider = strtolower(trim((string) ($rule->provider ?? '')));
        if ($contextProvider !== '') {
            if ($ruleProvider !== $contextProvider) {
                return false;
            }
        } elseif ($ruleProvider !== '') {
            return false;
        }

        if ($rule->type !== null && $rule->type !== '' && strcasecmp((string) $rule->type, (string) ($context['type'] ?? '')) !== 0) {
            return false;
        }

        return true;
    }

    private function scopeRank(CommissionRule $rule): int
    {
        if ($rule->merchant_id !== null) {
            return 3;
        }

        return $rule->vendor_id !== null ? 2 : 1;
    }

    public function rulesForVendor(int $vendorId): Collection
    {
        return $this->loadCandidates($vendorId);
    }

    private function loadCandidates(int $vendorId): Collection
    {
        return CommissionRule::query()
            ->where('status', 'active')
            ->where(function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId)->orWhereNull('vendor_id');
            })
            ->get();
    }

    private function existingEntry(string $sourceType, int $sourceId): ?CommissionEntry
    {
        return CommissionEntry::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->first();
    }

    private function isDuplicate(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? '';

        return in_array($sqlState, ['23000', '23505'], true);
    }
}
