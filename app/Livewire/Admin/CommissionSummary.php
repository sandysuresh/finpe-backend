<?php

namespace App\Livewire\Admin;

use App\Models\CommissionEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CommissionSummary extends Component
{
    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('dashboard')) {
            abort(403);
        }
    }

    public function render()
    {
        $recorded = CommissionEntry::query()->where('commission_entries.status', CommissionEntry::STATUS_RECORDED);

        $total = (float) (clone $recorded)->sum('commission_amount');

        $rows = (clone $recorded)
            ->leftJoin('commission_rules', 'commission_rules.id', '=', 'commission_entries.commission_rule_id')
            ->leftJoin('vendors', 'vendors.id', '=', 'commission_entries.vendor_id')
            ->select([
                'commission_entries.vendor_id',
                'vendors.business_name',
                'vendors.vendor_code',
                'commission_rules.provider',
                DB::raw("count(distinct case when commission_entries.source_type = 'transactions' then commission_entries.source_id end) as payout_count"),
                DB::raw('sum(commission_entries.commission_amount) as total_commission'),
            ])
            ->groupBy('commission_entries.vendor_id', 'vendors.business_name', 'vendors.vendor_code', 'commission_rules.provider')
            ->orderBy('vendors.business_name')
            ->get();

        return view('livewire.admin.commission-summary', [
            'total' => $total,
            'rows' => $rows,
        ])->layout('layouts.admin', ['title' => 'Commission Summary']);
    }
}
