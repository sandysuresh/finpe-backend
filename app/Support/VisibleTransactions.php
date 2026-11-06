<?php

namespace App\Support;

use App\Models\AlliancePePayout;
use App\Models\CommissionEntry;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Services\AlliancePe\AlliancePePayoutService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VisibleTransactions
{
    /**
     * @param  array{vendor_id?: int|string|null, search?: string, status?: string, service?: string, provider?: string, date_from?: string, date_to?: string, type?: string, payouts_only?: bool, always_include_alliance?: bool}  $filters
     */
    public function paginate(array $filters, int $perPage, int $page, string $pageName = 'page'): LengthAwarePaginator
    {
        $query = $this->indexQuery($filters)->orderByDesc('created_at')->orderByDesc('id');
        $total = (clone $this->indexQuery($filters))->count();
        $rows = (clone $query)->forPage(max(1, $page), $perPage)->get();

        return new LengthAwarePaginator(
            $this->hydrate($rows),
            $total,
            $perPage,
            max(1, $page),
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => $pageName]
        );
    }

    /**
     * @param  array{vendor_id?: int|string|null, search?: string, status?: string, service?: string, provider?: string, date_from?: string, date_to?: string, type?: string, payouts_only?: bool, always_include_alliance?: bool}  $filters
     */
    public function collect(array $filters): Collection
    {
        $rows = $this->indexQuery($filters)->orderByDesc('created_at')->orderByDesc('id')->get();

        return $this->hydrate($rows);
    }

    /**
     * @param  array{vendor_id?: int|string|null, search?: string, status?: string, service?: string, provider?: string, date_from?: string, date_to?: string, type?: string, payouts_only?: bool, always_include_alliance?: bool}  $filters
     */
    public function recent(array $filters, int $limit): Collection
    {
        $rows = $this->indexQuery($filters)->orderByDesc('created_at')->orderByDesc('id')->limit($limit)->get();

        return $this->hydrate($rows);
    }

    /**
     * @param  array{vendor_id?: int|string|null, search?: string, status?: string, service?: string, provider?: string, date_from?: string, date_to?: string, type?: string, payouts_only?: bool, always_include_alliance?: bool}  $filters
     * @return array{total: int, success: int, failed: int, pending: int, volume: float, amount: float, charge: float, commission: float}
     */
    public function summary(array $filters, bool $includeCommission = true): array
    {
        $transactions = $this->includeTransactions($filters) ? $this->transactionQuery($filters) : null;
        $alliance = $this->includeAlliance($filters) ? $this->allianceQuery($filters) : null;

        $success = $transactions ? (clone $transactions)->where('transactions.status', 'success')->count() : 0;
        $completed = $alliance ? (clone $alliance)->where('alliancepe_payouts.status', 'completed')->count() : 0;
        $failed = ($transactions ? (clone $transactions)->where('transactions.status', 'failed')->count() : 0)
            + ($alliance ? (clone $alliance)->where('alliancepe_payouts.status', 'failed')->count() : 0);
        $pending = ($transactions ? (clone $transactions)->where('transactions.status', 'pending')->count() : 0)
            + ($alliance ? (clone $alliance)->whereIn('alliancepe_payouts.status', ['pending', 'processing'])->count() : 0);
        $volume = ($transactions ? (float) (clone $transactions)->where('transactions.status', 'success')->sum('transactions.amount') : 0)
            + ($alliance ? (float) (clone $alliance)->where('alliancepe_payouts.status', 'completed')->sum('alliancepe_payouts.amount') : 0);
        $amount = ($transactions ? (float) (clone $transactions)->sum('transactions.amount') : 0)
            + ($alliance ? (float) (clone $alliance)->sum('alliancepe_payouts.amount') : 0);
        $charge = ($transactions ? (float) (clone $transactions)->sum('transactions.payout_charge') : 0)
            + ($alliance ? (float) (clone $alliance)->sum('alliancepe_payouts.commission_amount') : 0);

        return [
            'total' => ($transactions ? (clone $transactions)->count() : 0) + ($alliance ? (clone $alliance)->count() : 0),
            'success' => $success + $completed,
            'failed' => $failed,
            'pending' => $pending,
            'volume' => round($volume, 2),
            'amount' => round($amount, 2),
            'charge' => round($charge, 2),
            'commission' => $includeCommission ? round($this->recordedCommission($transactions, $alliance), 2) : 0.0,
        ];
    }

    /**
     * @param  list<string>|null  $allowed
     * @return list<array{code: string, name: string}>
     */
    public function providers(?int $vendorId = null, ?array $allowed = null): array
    {
        $codes = Transaction::query()
            ->when($vendorId, fn (Builder $query) => $query->where('vendor_id', $vendorId))
            ->whereNotNull('payout_provider')
            ->where('payout_provider', '!=', '')
            ->distinct()
            ->orderBy('payout_provider')
            ->pluck('payout_provider')
            ->map(fn ($code) => strtolower((string) $code))
            ->all();

        $hasAlliance = AlliancePePayout::query()
            ->when($vendorId, fn (Builder $query) => $query->where('vendor_id', $vendorId))
            ->exists();
        if ($hasAlliance) {
            $codes[] = CommissionProviders::ALLIANCEPE;
        }

        $options = [];
        foreach (array_values(array_unique($codes)) as $code) {
            if (is_array($allowed) && ! in_array($code, $allowed, true)) {
                continue;
            }
            $options[] = ['code' => $code, 'name' => CommissionProviders::name($code) ?: $code];
        }

        return $options;
    }

    /**
     * @param  Collection<int, mixed>|array<int, mixed>  $references
     */
    /**
     * @param  list<string>|null  $allowed
     */
    public function payoutsByReference(int $vendorId, Collection|array $references, ?array $allowed = null): Collection
    {
        $refs = collect($references)->filter()->unique()->values();
        if ($refs->isEmpty()) {
            return collect();
        }

        $transactions = Transaction::query()
            ->where('vendor_id', $vendorId)
            ->where('type', 'payout')
            ->whereIn('reference', $refs)
            ->when(is_array($allowed) && ! in_array(CommissionProviders::VIMOPAY, $allowed, true), function (Builder $query) {
                $query->where(function (Builder $inner) {
                    $inner->whereNull('payout_provider')
                        ->orWhere('payout_provider', '')
                        ->orWhereRaw('LOWER(payout_provider) <> ?', [CommissionProviders::VIMOPAY]);
                });
            })
            ->get()
            ->keyBy('reference');

        $alliance = is_array($allowed) && ! in_array(CommissionProviders::ALLIANCEPE, $allowed, true)
            ? collect()
            : AlliancePePayout::query()
                ->with('vendor')
                ->where('vendor_id', $vendorId)
                ->whereIn('merchant_order_id', $refs)
                ->get();
        $entries = $this->allianceEntries($alliance->pluck('id')->all());

        foreach ($alliance as $payout) {
            if ($transactions->has($payout->merchant_order_id)) {
                continue;
            }
            $transactions->put($payout->merchant_order_id, $this->presentAlliance($payout, $entries->get($payout->id)));
        }

        return $transactions;
    }

    /**
     * @param  list<string>|null  $allowed
     */
    public function findForVendor(int $vendorId, int $id, string $source, ?array $allowed = null): Transaction|VisiblePayoutRow|null
    {
        if ($source === 'alliancepe') {
            if (is_array($allowed) && ! in_array(CommissionProviders::ALLIANCEPE, $allowed, true)) {
                return null;
            }
            $payout = AlliancePePayout::query()->with('vendor')->where('vendor_id', $vendorId)->find($id);

            return $payout ? $this->presentAlliance($payout, $this->allianceEntries([$payout->id])->get($payout->id)) : null;
        }

        $txn = Transaction::query()
            ->with(['vendor', 'commissionEntry'])
            ->where('vendor_id', $vendorId)
            ->where('type', 'payout')
            ->find($id);
        if ($txn && is_array($allowed) && ! in_array(CommissionProviders::VIMOPAY, $allowed, true)
            && strtolower((string) $txn->payout_provider) === CommissionProviders::VIMOPAY) {
            return null;
        }

        return $txn ? $this->presentTransaction($txn) : null;
    }

    /**
     * @return list<string>
     */
    public static function allianceStatuses(string $status): array
    {
        return match ($status) {
            'success' => ['completed'],
            'pending' => ['pending', 'processing'],
            'failed' => ['failed'],
            'reversed' => ['cancelled'],
            'refund', 'refunded' => [],
            default => [$status],
        };
    }

    public function constrainLedger(object $query, string $status, string $type): void
    {
        $query->where(function ($outer) use ($status, $type) {
            $outer->whereExists(function ($sub) use ($status, $type) {
                $sub->selectRaw('1')
                    ->from('transactions')
                    ->whereColumn('transactions.vendor_id', 'wallet_ledger.vendor_id')
                    ->whereColumn('transactions.reference', 'wallet_ledger.reference');
                $this->applyLedgerTransactionStatus($sub, $status);
                if ($type !== '') {
                    $sub->where('transactions.type', $type);
                }
            });

            if ($type === '' || $type === 'payout') {
                $outer->orWhereExists(function ($sub) use ($status) {
                    $sub->selectRaw('1')
                        ->from('alliancepe_payouts')
                        ->whereColumn('alliancepe_payouts.vendor_id', 'wallet_ledger.vendor_id')
                        ->whereColumn('alliancepe_payouts.merchant_order_id', 'wallet_ledger.reference');
                    $this->applyLedgerAllianceStatus($sub, $status);
                });
            }
        });
    }

    public static function totalDebited(?string $walletState, mixed $stored, mixed $amount, mixed $commission): float
    {
        if ($walletState === 'not_debited') {
            return 0.0;
        }
        if ($stored === null || $stored === '') {
            return round((float) $amount + (float) $commission, 2);
        }

        return round((float) $stored, 2);
    }

    /**
     * @param  array{vendor_id?: int|string|null, search?: string, status?: string, service?: string, provider?: string, date_from?: string, date_to?: string, type?: string, payouts_only?: bool, always_include_alliance?: bool}  $filters
     */
    private function indexQuery(array $filters)
    {
        $parts = [];
        if ($this->includeTransactions($filters)) {
            $parts[] = $this->transactionQuery($filters)
                ->selectRaw("transactions.id as id, 'transaction' as source, transactions.created_at as created_at")
                ->toBase();
        }
        if ($this->includeAlliance($filters)) {
            $parts[] = $this->allianceQuery($filters)
                ->selectRaw("alliancepe_payouts.id as id, 'alliancepe' as source, alliancepe_payouts.created_at as created_at")
                ->toBase();
        }

        if ($parts === []) {
            return DB::query()->from('transactions')->whereRaw('1 = 0')->selectRaw("id, 'transaction' as source, created_at");
        }

        $union = array_shift($parts);
        foreach ($parts as $part) {
            $union->unionAll($part);
        }

        return DB::query()->fromSub($union, 'visible_transactions');
    }

    /**
     * @param  array{vendor_id?: int|string|null, search?: string, status?: string, service?: string, provider?: string, date_from?: string, date_to?: string, type?: string, payouts_only?: bool, always_include_alliance?: bool}  $filters
     */
    private function includeTransactions(array $filters): bool
    {
        return strtolower(trim((string) ($filters['provider'] ?? ''))) !== CommissionProviders::ALLIANCEPE;
    }

    /**
     * @param  array{vendor_id?: int|string|null, search?: string, status?: string, service?: string, provider?: string, date_from?: string, date_to?: string, type?: string, payouts_only?: bool, always_include_alliance?: bool}  $filters
     */
    private function includeAlliance(array $filters): bool
    {
        if (! $this->allowsProvider($filters, CommissionProviders::ALLIANCEPE)) {
            return false;
        }

        $type = (string) ($filters['type'] ?? '');
        if ($type !== '' && $type !== 'payout') {
            return false;
        }

        $provider = strtolower(trim((string) ($filters['provider'] ?? '')));
        if ($provider === '' || $provider === CommissionProviders::ALLIANCEPE) {
            return true;
        }

        return (bool) ($filters['always_include_alliance'] ?? false);
    }

    /**
     * @param  array{vendor_id?: int|string|null, search?: string, status?: string, service?: string, provider?: string, date_from?: string, date_to?: string, type?: string, payouts_only?: bool, always_include_alliance?: bool}  $filters
     */
    private function transactionQuery(array $filters): Builder
    {
        $provider = strtolower(trim((string) ($filters['provider'] ?? '')));
        $status = trim((string) ($filters['status'] ?? ''));
        $type = (string) ($filters['type'] ?? '');

        return Transaction::query()
            ->when(($filters['vendor_id'] ?? '') !== '' && ($filters['vendor_id'] ?? null) !== null, fn (Builder $query) => $query->where('transactions.vendor_id', (int) $filters['vendor_id']))
            ->when(($filters['payouts_only'] ?? false) || $type === 'payout', fn (Builder $query) => $query->where('transactions.type', 'payout'))
            ->when($type !== '' && $type !== 'payout', fn (Builder $query) => $query->where('transactions.type', $type))
            ->when($provider !== '' && $provider !== CommissionProviders::ALLIANCEPE, fn (Builder $query) => $query->where('transactions.payout_provider', $provider))
            ->when(! $this->allowsProvider($filters, CommissionProviders::VIMOPAY), function (Builder $query) {
                $query->where(function (Builder $inner) {
                    $inner->whereNull('transactions.payout_provider')
                        ->orWhere('transactions.payout_provider', '')
                        ->orWhereRaw('LOWER(transactions.payout_provider) <> ?', [CommissionProviders::VIMOPAY]);
                });
            })
            ->when(($filters['service'] ?? '') !== '', fn (Builder $query) => $query->where('transactions.service', $filters['service']))
            ->when(($filters['date_from'] ?? '') !== '', fn (Builder $query) => $query->whereDate('transactions.created_at', '>=', $filters['date_from']))
            ->when(($filters['date_to'] ?? '') !== '', fn (Builder $query) => $query->whereDate('transactions.created_at', '<=', $filters['date_to']))
            ->when($status !== '', fn (Builder $query) => $this->constrainTransactionStatus($query, $status))
            ->when(($filters['search'] ?? '') !== '', fn (Builder $query) => $this->searchTransactions($query, (string) $filters['search']));
    }

    /**
     * @param  array{vendor_id?: int|string|null, search?: string, status?: string, service?: string, provider?: string, date_from?: string, date_to?: string, type?: string, payouts_only?: bool, always_include_alliance?: bool}  $filters
     */
    private function allianceQuery(array $filters): Builder
    {
        $status = trim((string) ($filters['status'] ?? ''));

        return AlliancePePayout::query()
            ->when(($filters['vendor_id'] ?? '') !== '' && ($filters['vendor_id'] ?? null) !== null, fn (Builder $query) => $query->where('alliancepe_payouts.vendor_id', (int) $filters['vendor_id']))
            ->when(($filters['service'] ?? '') !== '', fn (Builder $query) => $query->where('alliancepe_payouts.transfer_mode', $filters['service']))
            ->when(($filters['date_from'] ?? '') !== '', fn (Builder $query) => $query->whereDate('alliancepe_payouts.created_at', '>=', $filters['date_from']))
            ->when(($filters['date_to'] ?? '') !== '', fn (Builder $query) => $query->whereDate('alliancepe_payouts.created_at', '<=', $filters['date_to']))
            ->when($status !== '', fn (Builder $query) => $this->constrainAllianceStatus($query, $status))
            ->when(($filters['search'] ?? '') !== '', fn (Builder $query) => $this->searchAlliance($query, (string) $filters['search']));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function allowsProvider(array $filters, string $provider): bool
    {
        if (! array_key_exists('allowed_providers', $filters)) {
            return true;
        }

        return in_array($provider, array_map(
            fn ($code) => strtolower(trim((string) $code)),
            (array) $filters['allowed_providers']
        ), true);
    }

    private function constrainTransactionStatus(Builder $query, string $status): void
    {
        if ($status === 'reversed') {
            $query->whereIn('transactions.status', ['reversed', 'refund', 'refunded']);

            return;
        }

        if (in_array($status, ['processing', 'completed', 'cancelled', 'on_hold', 'rejected'], true)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where('transactions.status', $status);
    }

    private function constrainAllianceStatus(Builder $query, string $status): void
    {
        $statuses = self::allianceStatuses($status);
        if ($statuses === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('alliancepe_payouts.status', $statuses);
    }

    private function searchTransactions(Builder $query, string $search): void
    {
        $term = '%'.$search.'%';
        $query->where(function (Builder $inner) use ($term) {
            $inner->where('transactions.reference', 'like', $term)
                ->orWhere('transactions.merchant_ref', 'like', $term)
                ->orWhere('transactions.beneficiary_name', 'like', $term)
                ->orWhere('transactions.bank_reference', 'like', $term)
                ->orWhere('transactions.failure_reason', 'like', $term)
                ->when(Schema::hasColumn('transactions', 'rrn'), fn (Builder $query) => $query->orWhere('transactions.rrn', 'like', $term))
                ->orWhereHas('vendor', function (Builder $vendor) use ($term) {
                    $vendor->where('business_name', 'like', $term)
                        ->orWhere('vendor_code', 'like', $term);
                });
        });
    }

    private function searchAlliance(Builder $query, string $search): void
    {
        $term = '%'.$search.'%';
        $query->where(function (Builder $inner) use ($term) {
            $inner->where('alliancepe_payouts.merchant_order_id', 'like', $term)
                ->orWhere('alliancepe_payouts.provider_payout_id', 'like', $term)
                ->orWhere('alliancepe_payouts.utr_number', 'like', $term)
                ->orWhere('alliancepe_payouts.beneficiary_name', 'like', $term)
                ->orWhere('alliancepe_payouts.failure_reason', 'like', $term)
                ->orWhereHas('vendor', function (Builder $vendor) use ($term) {
                    $vendor->where('business_name', 'like', $term)
                        ->orWhere('vendor_code', 'like', $term);
                });
        });
    }

    private function hydrate(Collection $rows): Collection
    {
        $transactionIds = $rows->where('source', 'transaction')->pluck('id')->all();
        $allianceIds = $rows->where('source', 'alliancepe')->pluck('id')->all();
        $transactions = Transaction::query()->with(['vendor', 'commissionEntry'])->whereIn('id', $transactionIds)->get()->keyBy('id');
        $alliance = AlliancePePayout::query()->with('vendor')->whereIn('id', $allianceIds)->get()->keyBy('id');
        $entries = $this->allianceEntries($allianceIds);

        return $rows->map(function (object $row) use ($transactions, $alliance, $entries) {
            if ($row->source === 'alliancepe') {
                $payout = $alliance->get($row->id);

                return $payout ? $this->presentAlliance($payout, $entries->get($payout->id)) : null;
            }

            $txn = $transactions->get($row->id);

            return $txn ? $this->presentTransaction($txn) : null;
        })->filter()->values();
    }

    private function presentTransaction(Transaction $txn): Transaction
    {
        $txn->setAttribute('source', 'transaction');
        $total = $txn->type === 'payout'
            ? round((float) $txn->amount + (float) $txn->payout_charge, 2)
            : round((float) $txn->amount, 2);
        $txn->setAttribute('total_debited', $total);
        $txn->setAttribute('recorded_commission', null);

        return $txn;
    }

    private function presentAlliance(AlliancePePayout $payout, ?CommissionEntry $entry): VisiblePayoutRow
    {
        $commission = round((float) $payout->commission_amount, 2);
        $recorded = $entry ? round((float) $entry->commission_amount, 2) : null;
        $snapshot = (object) [
            'commission_amount' => $recorded ?? $commission,
            'calc_type' => $payout->commission_type,
            'rate_value' => $payout->commission_rate,
            'status' => $entry?->status ?? 'snapshot',
            'created_at' => $entry?->created_at ?? $payout->created_at,
        ];

        return new VisiblePayoutRow(
            id: (int) $payout->id,
            source: 'alliancepe',
            reference: (string) $payout->merchant_order_id,
            merchant_ref: $payout->merchant_order_id,
            vendor_id: $payout->vendor_id ? (int) $payout->vendor_id : null,
            vendor: $payout->vendor,
            payout_provider: CommissionProviders::ALLIANCEPE,
            type: 'payout',
            service: $payout->transfer_mode,
            amount: round((float) $payout->amount, 2),
            payout_charge: $commission,
            commission_amount: $commission,
            total_debited: self::totalDebited($payout->wallet_state, $payout->total_debited, $payout->amount, $payout->commission_amount),
            beneficiary_name: $payout->beneficiary_name,
            account_number: $payout->account_number,
            ifsc_code: $payout->ifsc_code,
            bank_name: $payout->bank_name,
            beneficiary_bank_code: null,
            beneficiary_mobile: $payout->phone,
            beneficiary_location: null,
            payment_purpose: $payout->remarks,
            bank_reference: $payout->provider_payout_id,
            rrn: $payout->utr_number,
            status: $payout->status,
            failure_reason: $payout->failure_reason,
            commissionEntry: $snapshot,
            recorded_commission: $recorded,
            created_at: $payout->created_at ? Carbon::parse($payout->created_at) : null,
            updated_at: $payout->updated_at ? Carbon::parse($payout->updated_at) : null,
        );
    }

    /**
     * @param  array<int, mixed>  $ids
     */
    private function allianceEntries(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return CommissionEntry::query()
            ->where('source_type', AlliancePePayoutService::COMMISSION_SOURCE)
            ->whereIn('source_id', $ids)
            ->get()
            ->keyBy('source_id');
    }

    private function recordedCommission(?Builder $transactions, ?Builder $alliance): float
    {
        $sum = 0.0;
        if ($transactions) {
            $sum += (float) CommissionEntry::query()
                ->where('source_type', 'transactions')
                ->whereIn('source_id', (clone $transactions)->select('transactions.id'))
                ->sum('commission_amount');
        }
        if ($alliance) {
            $sum += (float) CommissionEntry::query()
                ->where('source_type', AlliancePePayoutService::COMMISSION_SOURCE)
                ->whereIn('source_id', (clone $alliance)->select('alliancepe_payouts.id'))
                ->sum('commission_amount');
        }

        return $sum;
    }

    private function applyLedgerTransactionStatus(object $query, string $status): void
    {
        if ($status === '') {
            return;
        }
        if ($status === 'reversed') {
            $query->whereIn('transactions.status', ['reversed', 'refund', 'refunded']);

            return;
        }
        if (in_array($status, ['processing', 'completed', 'cancelled', 'on_hold', 'rejected'], true)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where('transactions.status', $status);
    }

    private function applyLedgerAllianceStatus(object $query, string $status): void
    {
        if ($status === '') {
            return;
        }
        $statuses = self::allianceStatuses($status);
        if ($statuses === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('alliancepe_payouts.status', $statuses);
    }
}
