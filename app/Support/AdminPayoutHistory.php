<?php

namespace App\Support;

use App\Models\AlliancePePayout;
use App\Models\Transaction;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AdminPayoutHistory
{
    /**
     * @param  array{status?: string, provider?: string, search?: string, date_from?: string, date_to?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $page = $this->query($filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
        $vendors = $this->vendors($page->getCollection()->pluck('vendor_id')->all());

        return $page->through(fn (object $row) => $this->present($row, $vendors->get($row->vendor_id)));
    }

    /**
     * @param  array{status?: string, provider?: string, search?: string, date_from?: string, date_to?: string}  $filters
     * @return array<int, object>
     */
    public function all(array $filters): array
    {
        $rows = $this->query($filters)->orderByDesc('created_at')->orderByDesc('id')->get();
        $vendors = $this->vendors($rows->pluck('vendor_id')->all());

        return $rows->map(fn (object $row) => $this->present($row, $vendors->get($row->vendor_id)))->all();
    }

    /**
     * @param  array<int, mixed>  $ids
     */
    private function vendors(array $ids): \Illuminate\Support\Collection
    {
        return Vendor::query()->whereIn('id', array_filter($ids))->get()->keyBy('id');
    }

    /**
     * @param  array{status?: string, provider?: string, search?: string, date_from?: string, date_to?: string}  $filters
     * @return array{count: int, amount: string, charge: string}
     */
    public function summary(array $filters): array
    {
        $row = DB::query()
            ->fromSub($this->query($filters), 'payout_history')
            ->selectRaw('COUNT(*) as payout_count, COALESCE(SUM(amount), 0) as payout_amount, COALESCE(SUM(commission_amount), 0) as payout_charge')
            ->first();

        return [
            'count' => (int) ($row->payout_count ?? 0),
            'amount' => number_format((float) ($row->payout_amount ?? 0), 2),
            'charge' => number_format((float) ($row->payout_charge ?? 0), 2),
        ];
    }

    public function find(string $source, int $id): ?object
    {
        if ($source === 'alliancepe') {
            $payout = AlliancePePayout::query()->with('vendor')->find($id);
            if (! $payout) {
                return null;
            }

            return $this->present((object) [
                'source' => 'alliancepe',
                'id' => $payout->id,
                'reference' => $payout->merchant_order_id,
                'merchant_ref' => $payout->merchant_order_id,
                'vendor_id' => $payout->vendor_id,
                'payout_provider' => CommissionProviders::ALLIANCEPE,
                'amount' => $payout->amount,
                'commission_amount' => $payout->commission_amount,
                'total_debited' => $this->allianceTotal($payout->wallet_state, $payout->total_debited, $payout->amount, $payout->commission_amount),
                'service' => $payout->transfer_mode,
                'beneficiary_name' => $payout->beneficiary_name,
                'account_number' => $payout->account_number,
                'ifsc_code' => $payout->ifsc_code,
                'bank_label' => $payout->bank_name,
                'provider_reference' => $payout->provider_payout_id,
                'status' => $payout->status,
                'failure_reason' => $payout->failure_reason,
                'created_at' => $payout->created_at,
                'updated_at' => $payout->updated_at,
                'mobile' => $payout->phone,
                'purpose' => $payout->remarks,
            ], $payout->vendor);
        }

        $txn = Transaction::query()->with('vendor')->where('type', 'payout')->find($id);
        if (! $txn) {
            return null;
        }

        return $this->present((object) [
            'source' => 'transaction',
            'id' => $txn->id,
            'reference' => $txn->reference,
            'merchant_ref' => $txn->merchant_ref,
            'vendor_id' => $txn->vendor_id,
            'payout_provider' => $txn->payout_provider,
            'amount' => $txn->amount,
            'commission_amount' => $txn->payout_charge,
            'total_debited' => round((float) $txn->amount + (float) $txn->payout_charge, 2),
            'service' => $txn->service,
            'beneficiary_name' => $txn->beneficiary_name,
            'account_number' => $txn->account_number,
            'ifsc_code' => $txn->ifsc_code,
            'bank_label' => $txn->beneficiary_bank_code ?: $txn->bank_name,
            'provider_reference' => $txn->bank_reference,
            'status' => $txn->status,
            'failure_reason' => $txn->failure_reason,
            'created_at' => $txn->created_at,
            'updated_at' => $txn->updated_at,
            'mobile' => $txn->beneficiary_mobile,
            'purpose' => $txn->payment_purpose,
            'location' => $txn->beneficiary_location,
        ], $txn->vendor);
    }

    /**
     * @param  array{status?: string, provider?: string, search?: string, date_from?: string, date_to?: string}  $filters
     */
    private function query(array $filters): Builder
    {
        $provider = strtolower(trim((string) ($filters['provider'] ?? '')));
        $parts = [];
        if ($provider !== CommissionProviders::ALLIANCEPE) {
            $parts[] = $this->transactions($filters);
        }
        if ($provider === '' || $provider === CommissionProviders::ALLIANCEPE) {
            $parts[] = $this->alliancePe($filters);
        }

        $union = array_shift($parts);
        foreach ($parts as $part) {
            $union->unionAll($part);
        }

        return DB::query()->fromSub($union, 'payout_history');
    }

    /**
     * @param  array{status?: string, provider?: string, search?: string, date_from?: string, date_to?: string}  $filters
     */
    private function transactions(array $filters): Builder
    {
        $status = trim((string) ($filters['status'] ?? ''));
        $provider = strtolower(trim((string) ($filters['provider'] ?? '')));

        return DB::table('transactions')
            ->where('type', 'payout')
            ->when($provider !== '' && $provider !== CommissionProviders::ALLIANCEPE, fn (Builder $query) => $query->where('payout_provider', $provider))
            ->when(in_array($status, ['pending', 'success', 'failed'], true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($status, ['processing', 'completed', 'cancelled'], true), fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->when(($filters['date_from'] ?? '') !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(($filters['date_to'] ?? '') !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->when(($filters['search'] ?? '') !== '', function (Builder $query) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $query->where(function (Builder $inner) use ($term) {
                    $inner->where('reference', 'like', $term)
                        ->orWhere('merchant_ref', 'like', $term)
                        ->orWhere('bank_reference', 'like', $term)
                        ->orWhere('beneficiary_name', 'like', $term);
                });
            })
            ->select([
                'id',
                DB::raw("'transaction' as source"),
                'reference',
                'merchant_ref',
                'vendor_id',
                'payout_provider',
                'amount',
                'payout_charge as commission_amount',
                DB::raw('(amount + payout_charge) as total_debited'),
                'service',
                'beneficiary_name',
                'account_number',
                'ifsc_code',
                DB::raw('COALESCE(NULLIF(beneficiary_bank_code, \'\'), bank_name) as bank_label'),
                'bank_reference as provider_reference',
                'status',
                'failure_reason',
                'created_at',
            ]);
    }

    /**
     * @param  array{status?: string, provider?: string, search?: string, date_from?: string, date_to?: string}  $filters
     */
    private function alliancePe(array $filters): Builder
    {
        $status = trim((string) ($filters['status'] ?? ''));
        $allianceStatus = match ($status) {
            'success' => 'completed',
            '' => null,
            default => $status,
        };

        return DB::table('alliancepe_payouts')
            ->when($allianceStatus, fn (Builder $query) => $query->where('status', $allianceStatus))
            ->when(($filters['date_from'] ?? '') !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(($filters['date_to'] ?? '') !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->when(($filters['search'] ?? '') !== '', function (Builder $query) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $query->where(function (Builder $inner) use ($term, $filters) {
                    $inner->where('merchant_order_id', 'like', $term)
                        ->orWhere('provider_payout_id', 'like', $term)
                        ->orWhere('beneficiary_name', 'like', $term)
                        ->orWhere('failure_reason', 'like', $term);
                    if (ctype_digit($filters['search'])) {
                        $inner->orWhere('id', (int) $filters['search']);
                    }
                });
            })
            ->select([
                'id',
                DB::raw("'alliancepe' as source"),
                'merchant_order_id as reference',
                'merchant_order_id as merchant_ref',
                'vendor_id',
                DB::raw("'".CommissionProviders::ALLIANCEPE."' as payout_provider"),
                'amount',
                'commission_amount',
                DB::raw("CASE WHEN wallet_state = 'not_debited' THEN 0 WHEN total_debited IS NULL THEN amount + commission_amount ELSE total_debited END as total_debited"),
                'transfer_mode as service',
                'beneficiary_name',
                'account_number',
                'ifsc_code',
                'bank_name as bank_label',
                'provider_payout_id as provider_reference',
                'status',
                'failure_reason',
                'created_at',
            ]);
    }

    private function allianceTotal(mixed $walletState, mixed $total, mixed $amount, mixed $commission): float
    {
        if ((string) $walletState === 'not_debited') {
            return 0.0;
        }
        if ($total === null || $total === '') {
            return round((float) $amount + (float) $commission, 2);
        }

        return round((float) $total, 2);
    }

    private function present(object $row, ?Vendor $vendor = null): object
    {
        if (! $vendor && isset($row->vendor_id)) {
            $vendor = Vendor::query()->find($row->vendor_id);
        }
        $created = $row->created_at instanceof Carbon ? $row->created_at : Carbon::parse($row->created_at);

        return (object) [
            'source' => $row->source,
            'id' => (int) $row->id,
            'reference' => $row->reference,
            'merchant_ref' => $row->merchant_ref,
            'vendor_id' => (int) $row->vendor_id,
            'vendor' => $vendor,
            'vendor_name' => $vendor?->business_name,
            'payout_provider' => $row->payout_provider,
            'provider_name' => CommissionProviders::name($row->payout_provider) ?: '—',
            'amount' => (float) $row->amount,
            'commission_amount' => (float) $row->commission_amount,
            'total_debited' => (float) $row->total_debited,
            'payout_charge' => (float) $row->commission_amount,
            'service' => $row->service,
            'beneficiary_name' => $row->beneficiary_name,
            'account_number' => $row->account_number,
            'ifsc_code' => $row->ifsc_code,
            'bank_name' => $row->bank_label,
            'beneficiary_bank_code' => $row->source === 'transaction' ? $row->bank_label : null,
            'bank_reference' => $row->provider_reference,
            'status' => $row->status,
            'failure_reason' => $row->failure_reason,
            'created_at' => $created,
            'updated_at' => isset($row->updated_at) && $row->updated_at ? ($row->updated_at instanceof Carbon ? $row->updated_at : Carbon::parse($row->updated_at)) : $created,
            'type' => 'payout',
            'beneficiary_mobile' => isset($row->mobile) ? $row->mobile : null,
            'beneficiary_location' => isset($row->location) ? $row->location : null,
            'payment_purpose' => isset($row->purpose) ? $row->purpose : null,
        ];
    }
}
