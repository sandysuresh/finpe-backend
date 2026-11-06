<?php

namespace App\Support;

use App\Models\Vendor;
use App\Models\VendorPayoutProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class AssignedPayoutProviders
{
    /**
     * @return list<string>
     */
    public static function codes(Vendor|int $vendor): array
    {
        $vendorId = $vendor instanceof Vendor ? (int) $vendor->id : $vendor;

        return VendorPayoutProvider::query()
            ->where('vendor_id', $vendorId)
            ->orderBy('provider_code')
            ->pluck('provider_code')
            ->map(fn ($code) => strtolower((string) $code))
            ->filter(fn (string $code) => self::known($code))
            ->unique()
            ->values()
            ->all();
    }

    public static function allows(Vendor|int $vendor, string $provider): bool
    {
        $provider = strtolower(trim($provider));

        return self::known($provider) && in_array($provider, self::codes($vendor), true);
    }

    public static function assertAllows(Vendor|int $vendor, string $provider): void
    {
        abort_unless(self::allows($vendor, $provider), 403, 'This payout vendor is not assigned to your account.');
    }

    public static function assertAny(Vendor|int $vendor): void
    {
        abort_unless(self::codes($vendor) !== [], 403, 'No payout vendor is assigned to your account.');
    }

    /**
     * @param  list<string>  $codes
     */
    public static function sync(Vendor|int $vendor, array $codes, ?int $adminId): void
    {
        $vendorId = $vendor instanceof Vendor ? (int) $vendor->id : $vendor;
        $codes = collect($codes)
            ->map(fn ($code) => strtolower(trim((string) $code)))
            ->filter(fn (string $code) => self::known($code))
            ->unique()
            ->values();

        DB::transaction(function () use ($vendorId, $codes, $adminId) {
            $existing = VendorPayoutProvider::query()->where('vendor_id', $vendorId);
            if ($codes->isEmpty()) {
                $existing->delete();

                return;
            }

            $existing->whereNotIn('provider_code', $codes->all())->delete();
            foreach ($codes as $code) {
                VendorPayoutProvider::query()->updateOrCreate(
                    ['vendor_id' => $vendorId, 'provider_code' => $code],
                    ['assigned_at' => now(), 'assigned_by' => $adminId],
                );
            }
        });
    }

    public static function excludeUnassignedTransactions(Builder|Relation $query, Vendor|int $vendor): void
    {
        if (self::allows($vendor, CommissionProviders::VIMOPAY)) {
            return;
        }

        $query->where(function (Builder|Relation $inner) {
            $inner->whereNull('payout_provider')
                ->orWhere('payout_provider', '')
                ->orWhereRaw('LOWER(payout_provider) <> ?', [CommissionProviders::VIMOPAY]);
        });
    }

    public static function restrictLedger(Builder|Relation $query, Vendor|int $vendor): void
    {
        if (! self::allows($vendor, CommissionProviders::ALLIANCEPE)) {
            $query->where(function (Builder|Relation $inner) {
                $inner->where(function (Builder|Relation $source) {
                    $source->whereNull('wallet_ledger.source')
                        ->orWhere('wallet_ledger.source', '!=', 'alliancepe');
                })->whereNotExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('alliancepe_payouts')
                        ->whereColumn('alliancepe_payouts.vendor_id', 'wallet_ledger.vendor_id')
                        ->whereColumn('alliancepe_payouts.merchant_order_id', 'wallet_ledger.reference');
                });
            });
        }

        if (! self::allows($vendor, CommissionProviders::VIMOPAY)) {
            $query->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('transactions')
                    ->whereColumn('transactions.vendor_id', 'wallet_ledger.vendor_id')
                    ->whereColumn('transactions.reference', 'wallet_ledger.reference')
                    ->whereRaw('LOWER(transactions.payout_provider) = ?', [CommissionProviders::VIMOPAY]);
            });
        }
    }

    /**
     * @return array{vimopay: int, alliancepe: int}
     */
    public static function backfill(): array
    {
        $inserted = [
            CommissionProviders::VIMOPAY => 0,
            CommissionProviders::ALLIANCEPE => 0,
        ];

        $vimopayIds = DB::table('vendor_api_access')
            ->where('is_enabled', true)
            ->where('api_code', 'payout-provider:'.CommissionProviders::VIMOPAY)
            ->pluck('vendor_id')
            ->merge(
                DB::table('vendor_api_access')
                    ->where('is_enabled', true)
                    ->where('api_code', 'payout')
                    ->pluck('vendor_id')
            )
            ->merge(
                DB::table('transactions')
                    ->whereRaw('LOWER(payout_provider) = ?', [CommissionProviders::VIMOPAY])
                    ->pluck('vendor_id')
            )
            ->unique()
            ->filter();

        foreach ($vimopayIds as $vendorId) {
            $inserted[CommissionProviders::VIMOPAY] += self::insertMissing((int) $vendorId, CommissionProviders::VIMOPAY);
        }

        $allianceIds = DB::table('alliancepe_payouts')->distinct()->pluck('vendor_id');
        foreach ($allianceIds as $vendorId) {
            if ($vendorId === null) {
                continue;
            }
            $inserted[CommissionProviders::ALLIANCEPE] += self::insertMissing((int) $vendorId, CommissionProviders::ALLIANCEPE);
        }

        return $inserted;
    }

    private static function insertMissing(int $vendorId, string $provider): int
    {
        $row = VendorPayoutProvider::query()->firstOrCreate(
            ['vendor_id' => $vendorId, 'provider_code' => $provider],
            ['assigned_at' => now(), 'assigned_by' => null],
        );

        return $row->wasRecentlyCreated ? 1 : 0;
    }

    private static function known(string $provider): bool
    {
        return in_array($provider, [CommissionProviders::VIMOPAY, CommissionProviders::ALLIANCEPE], true);
    }
}
