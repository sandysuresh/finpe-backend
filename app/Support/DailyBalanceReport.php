<?php

namespace App\Support;

use App\Models\Vendor;
use App\Models\WalletLedger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailyBalanceReport
{
    /**
     * One row per calendar day.
     * Opening is the ledger balance before that day, summed across the selected vendors.
     * Closing is opening + that day's credits − that day's debits.
     * A day with no ledger rows keeps the same opening and closing.
     *
     * @return array{days: Collection<int, array{date: string, opening: float, credit: float, debit: float, closing: float}>, vendors: Collection<int, array{date: string, vendor_id: int, vendor: string, vendor_code: string, opening: float, credit: float, debit: float, closing: float}>, opening: float, closing: float, credit: float, debit: float}
     */
    public function build(?int $vendorId, string $dateFrom, string $dateTo): array
    {
        $start = Carbon::parse($dateFrom)->startOfDay();
        $end = Carbon::parse($dateTo)->startOfDay();
        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }
        if ($start->diffInDays($end) > 62) {
            $end = $start->copy()->addDays(62);
        }

        $vendors = Vendor::query()
            ->when($vendorId, fn ($query) => $query->whereKey($vendorId))
            ->orderBy('business_name')
            ->orderBy('id')
            ->get(['id', 'business_name', 'vendor_code']);

        $carry = $this->openingBefore($vendors->pluck('id'), $start->toDateString());
        $movement = $this->movement($vendors->pluck('id'), $start->toDateString(), $end->toDateString());
        $days = collect();
        $lines = collect();
        $cursor = $start->copy();
        $guard = 0;

        while ($cursor->lte($end) && $guard < 63) {
            $date = $cursor->toDateString();
            $opening = round(array_sum($carry), 2);
            $credit = 0.0;
            $debit = 0.0;

            foreach ($vendors as $vendor) {
                $id = (int) $vendor->id;
                $day = $movement[$id.'|'.$date] ?? ['credit' => 0.0, 'debit' => 0.0];
                $vendorOpening = round((float) ($carry[$id] ?? 0), 2);
                $vendorClosing = round($vendorOpening + $day['credit'] - $day['debit'], 2);
                $carry[$id] = $vendorClosing;
                $credit += $day['credit'];
                $debit += $day['debit'];
                $lines->push([
                    'date' => $date,
                    'vendor_id' => $id,
                    'vendor' => $vendor->business_name,
                    'vendor_code' => $vendor->vendor_code,
                    'opening' => $vendorOpening,
                    'credit' => $day['credit'],
                    'debit' => $day['debit'],
                    'closing' => $vendorClosing,
                ]);
            }

            $days->push([
                'date' => $date,
                'opening' => $opening,
                'credit' => round($credit, 2),
                'debit' => round($debit, 2),
                'closing' => round($opening + $credit - $debit, 2),
            ]);

            $cursor->addDay();
            $guard++;
        }

        $first = $days->first();
        $last = $days->last();

        return [
            'days' => $days,
            'vendors' => $lines,
            'opening' => round((float) ($first['opening'] ?? 0), 2),
            'closing' => round((float) ($last['closing'] ?? 0), 2),
            'credit' => round((float) $days->sum('credit'), 2),
            'debit' => round((float) $days->sum('debit'), 2),
        ];
    }

    /**
     * @param  Collection<int, int>  $vendorIds
     * @return array<int, float>
     */
    private function openingBefore(Collection $vendorIds, string $date): array
    {
        $carry = [];
        foreach ($vendorIds as $id) {
            $carry[(int) $id] = 0.0;
        }
        if ($vendorIds->isEmpty()) {
            return $carry;
        }

        $ranked = WalletLedger::query()
            ->whereIn('vendor_id', $vendorIds)
            ->whereDate('created_at', '<', $date)
            ->select('vendor_id', 'balance_after')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY vendor_id ORDER BY created_at DESC, id DESC) as rn');

        $rows = DB::query()->fromSub($ranked->toBase(), 'ranked')->where('rn', 1)->get();
        foreach ($rows as $row) {
            $carry[(int) $row->vendor_id] = round((float) $row->balance_after, 2);
        }

        return $carry;
    }

    /**
     * @param  Collection<int, int>  $vendorIds
     * @return array<string, array{credit: float, debit: float}>
     */
    private function movement(Collection $vendorIds, string $from, string $to): array
    {
        if ($vendorIds->isEmpty()) {
            return [];
        }

        $rows = WalletLedger::query()
            ->whereIn('vendor_id', $vendorIds)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'vendor_id', 'type', 'amount', 'created_at']);

        $movement = [];
        foreach ($rows as $row) {
            $key = $row->vendor_id.'|'.$row->created_at->toDateString();
            $movement[$key] ??= ['credit' => 0.0, 'debit' => 0.0];
            $amount = round((float) $row->amount, 2);
            if ($row->type === 'credit') {
                $movement[$key]['credit'] = round($movement[$key]['credit'] + $amount, 2);
            } else {
                $movement[$key]['debit'] = round($movement[$key]['debit'] + $amount, 2);
            }
        }

        return $movement;
    }
}
