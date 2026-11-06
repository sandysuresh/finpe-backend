<?php

namespace App\Console\Commands;

use App\Models\AlliancePePayout;
use App\Services\AlliancePe\AlliancePePayoutService;
use Illuminate\Console\Command;

class ReconcileAlliancePePayouts extends Command
{
    protected $signature = 'alliancepe:reconcile-payouts';

    protected $description = 'Refresh non-terminal AlliancePe payouts from the status API';

    public function handle(AlliancePePayoutService $payouts): int
    {
        AlliancePePayout::query()
            ->whereIn('status', ['pending', 'processing', 'on_hold'])
            ->whereNotNull('provider_payout_id')
            ->orderBy('id')
            ->each(function (AlliancePePayout $payout) use ($payouts): void {
                $payouts->reconcile($payout);
            });

        return self::SUCCESS;
    }
}
