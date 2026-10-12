<?php

namespace App\Support;

use App\Models\CommissionEntry;
use App\Models\Transaction;
use App\Services\CommissionService;
use Illuminate\Database\Eloquent\Builder;

class RecordedCommission
{
    public static function amount(Transaction $transaction): ?float
    {
        $entry = $transaction->relationLoaded('commissionEntry')
            ? $transaction->getRelation('commissionEntry')
            : $transaction->commissionEntry()->where('vendor_id', $transaction->vendor_id)->first();

        if (! $entry instanceof CommissionEntry || (int) $entry->vendor_id !== (int) $transaction->vendor_id) {
            return null;
        }

        return round((float) $entry->commission_amount, 2);
    }

    public static function total(Builder $transactions, int $vendorId): float
    {
        $sum = CommissionEntry::query()
            ->where('vendor_id', $vendorId)
            ->where('source_type', CommissionService::SOURCE_TRANSACTIONS)
            ->whereIn('source_id', (clone $transactions)->select('transactions.id'))
            ->sum('commission_amount');

        return round((float) $sum, 2);
    }
}
