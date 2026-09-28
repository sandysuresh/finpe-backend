<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SampleWalletReport
{
    public static function vendors(): Collection
    {
        return collect([
            ['code' => 'VND-1001', 'name' => 'Himalaya Remit'],
            ['code' => 'VND-1002', 'name' => 'NepalLink Pay'],
            ['code' => 'VND-1003', 'name' => 'Everest Transfers'],
            ['code' => 'VND-1004', 'name' => 'Kathmandu Express'],
        ]);
    }

    public static function statement(?string $vendorCode, string $dateFrom, string $dateTo): array
    {
        $lines = self::lines($vendorCode, $dateFrom, $dateTo);

        $rows = self::vendors()
            ->when($vendorCode, fn (Collection $list) => $list->where('code', $vendorCode))
            ->map(fn (array $vendor) => self::totals($vendor, $lines->where('vendor_code', $vendor['code'])->values(), $dateFrom))
            ->values();

        return [
            'lines' => $lines,
            'rows' => $rows,
            'summary' => [
                'opening' => round((float) $rows->sum('opening'), 2),
                'credit' => round((float) $rows->sum('credit'), 2),
                'debit' => round((float) $rows->sum('debit'), 2),
                'closing' => round((float) $rows->sum('closing'), 2),
            ],
        ];
    }

    /**
     * One row per calendar day.
     * Opening of a day is the previous day's closing.
     * Closing of a day is opening + credit − debit.
     * Period opening is the first day's opening. Period closing is the last day's closing.
     */
    public static function daily(?string $vendorCode, string $dateFrom, string $dateTo): array
    {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->startOfDay();
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        $vendors = self::vendors()
            ->when($vendorCode, fn (Collection $list) => $list->where('code', $vendorCode))
            ->values();

        $days = collect();
        $cursor = $from->copy();
        $guard = 0;

        while ($cursor->lte($to) && $guard < 63) {
            $date = $cursor->toDateString();
            $opening = 0.0;
            $credit = 0.0;
            $debit = 0.0;

            foreach ($vendors as $vendor) {
                $dayLines = self::lines($vendor['code'], $date, $date);
                $opening += self::openingBefore($vendor['code'], $date);
                $credit += (float) $dayLines->where('type', 'credit')->sum('amount');
                $debit += (float) $dayLines->where('type', 'debit')->sum('amount');
            }

            $days->push([
                'date' => $date,
                'opening' => round($opening, 2),
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
            'opening' => round((float) ($first['opening'] ?? 0), 2),
            'closing' => round((float) ($last['closing'] ?? 0), 2),
            'credit' => round((float) $days->sum('credit'), 2),
            'debit' => round((float) $days->sum('debit'), 2),
        ];
    }

    public static function forAccount(string $code, string $name, string $dateFrom, string $dateTo): array
    {
        $samples = self::vendors()->values();
        $sample = $samples->get(abs(crc32($code)) % $samples->count());
        $report = self::vendor($sample['code'], $dateFrom, $dateTo);
        $report['code'] = $code;
        $report['name'] = $name;
        $report['lines'] = $report['lines']->map(function (array $line) use ($code, $name) {
            $line['vendor_code'] = $code;
            $line['vendor'] = $name;

            return $line;
        })->values();

        return $report;
    }

    public static function vendor(string $code, string $dateFrom, string $dateTo): ?array
    {
        $vendor = self::vendors()->firstWhere('code', $code);
        if (! $vendor) {
            return null;
        }

        $lines = self::lines($code, $dateFrom, $dateTo);

        return self::totals($vendor, $lines, $dateFrom) + ['lines' => $lines];
    }

    private static function totals(array $vendor, Collection $lines, string $dateFrom): array
    {
        $credit = round((float) $lines->where('type', 'credit')->sum('amount'), 2);
        $debit = round((float) $lines->where('type', 'debit')->sum('amount'), 2);
        $opening = $lines->isEmpty()
            ? self::openingBefore($vendor['code'], $dateFrom)
            : round((float) $lines->first()['balance_before'], 2);

        return [
            'code' => $vendor['code'],
            'name' => $vendor['name'],
            'opening' => $opening,
            'credit' => $credit,
            'debit' => $debit,
            'closing' => round($opening + $credit - $debit, 2),
        ];
    }

    private static function lines(?string $code, string $dateFrom, string $dateTo): Collection
    {
        return collect(self::ledger())
            ->when($code, fn (Collection $rows) => $rows->where('vendor_code', $code))
            ->when($dateFrom !== '', fn (Collection $rows) => $rows->filter(fn ($row) => $row['date'] >= $dateFrom))
            ->when($dateTo !== '', fn (Collection $rows) => $rows->filter(fn ($row) => $row['date'] <= $dateTo))
            ->values();
    }

    private static function openingBefore(string $code, string $dateFrom): float
    {
        $all = collect(self::ledger())->where('vendor_code', $code)->values();
        if ($all->isEmpty()) {
            return 0;
        }

        if ($dateFrom === '') {
            return round((float) $all->first()['balance_before'], 2);
        }

        $prior = $all->filter(fn ($row) => $row['date'] < $dateFrom);
        if ($prior->isEmpty()) {
            return round((float) $all->first()['balance_before'], 2);
        }

        return round((float) $prior->last()['balance_after'], 2);
    }

    private static function ledger(): array
    {
        return [
            ['date' => '2026-09-01', 'vendor_code' => 'VND-1001', 'vendor' => 'Himalaya Remit', 'type' => 'credit', 'amount' => 100000, 'balance_before' => 500000, 'balance_after' => 600000, 'reference' => 'TOP-9001', 'description' => 'Wallet top-up'],
            ['date' => '2026-09-08', 'vendor_code' => 'VND-1001', 'vendor' => 'Himalaya Remit', 'type' => 'debit', 'amount' => 50000, 'balance_before' => 600000, 'balance_after' => 550000, 'reference' => 'TXN-A10001', 'description' => 'IMPS payout'],
            ['date' => '2026-09-12', 'vendor_code' => 'VND-1001', 'vendor' => 'Himalaya Remit', 'type' => 'credit', 'amount' => 100000, 'balance_before' => 550000, 'balance_after' => 650000, 'reference' => 'TOP-9008', 'description' => 'Wallet top-up'],
            ['date' => '2026-09-18', 'vendor_code' => 'VND-1001', 'vendor' => 'Himalaya Remit', 'type' => 'debit', 'amount' => 75000, 'balance_before' => 650000, 'balance_after' => 575000, 'reference' => 'TXN-A10022', 'description' => 'NEFT payout'],

            ['date' => '2026-09-02', 'vendor_code' => 'VND-1002', 'vendor' => 'NepalLink Pay', 'type' => 'credit', 'amount' => 50000, 'balance_before' => 180000, 'balance_after' => 230000, 'reference' => 'TOP-9010', 'description' => 'Wallet top-up'],
            ['date' => '2026-09-09', 'vendor_code' => 'VND-1002', 'vendor' => 'NepalLink Pay', 'type' => 'debit', 'amount' => 42000, 'balance_before' => 230000, 'balance_after' => 188000, 'reference' => 'TXN-B20011', 'description' => 'IMPS payout'],
            ['date' => '2026-09-16', 'vendor_code' => 'VND-1002', 'vendor' => 'NepalLink Pay', 'type' => 'credit', 'amount' => 25000, 'balance_before' => 188000, 'balance_after' => 213000, 'reference' => 'TOP-9016', 'description' => 'Wallet top-up'],
            ['date' => '2026-09-22', 'vendor_code' => 'VND-1002', 'vendor' => 'NepalLink Pay', 'type' => 'debit', 'amount' => 50000, 'balance_before' => 213000, 'balance_after' => 163000, 'reference' => 'TXN-B20040', 'description' => 'RTGS payout'],

            ['date' => '2026-09-03', 'vendor_code' => 'VND-1003', 'vendor' => 'Everest Transfers', 'type' => 'credit', 'amount' => 40000, 'balance_before' => 90000, 'balance_after' => 130000, 'reference' => 'TOP-9020', 'description' => 'Wallet top-up'],
            ['date' => '2026-09-14', 'vendor_code' => 'VND-1003', 'vendor' => 'Everest Transfers', 'type' => 'debit', 'amount' => 40000, 'balance_before' => 130000, 'balance_after' => 90000, 'reference' => 'TXN-C30007', 'description' => 'IMPS payout'],

            ['date' => '2026-09-04', 'vendor_code' => 'VND-1004', 'vendor' => 'Kathmandu Express', 'type' => 'credit', 'amount' => 15000, 'balance_before' => 25000, 'balance_after' => 40000, 'reference' => 'TOP-9030', 'description' => 'Wallet top-up'],
            ['date' => '2026-09-11', 'vendor_code' => 'VND-1004', 'vendor' => 'Kathmandu Express', 'type' => 'debit', 'amount' => 18000, 'balance_before' => 40000, 'balance_after' => 22000, 'reference' => 'TXN-D40003', 'description' => 'NEFT payout'],
            ['date' => '2026-09-21', 'vendor_code' => 'VND-1004', 'vendor' => 'Kathmandu Express', 'type' => 'debit', 'amount' => 20000, 'balance_before' => 22000, 'balance_after' => 2000, 'reference' => 'TXN-D40019', 'description' => 'IMPS payout'],
        ];
    }
}
