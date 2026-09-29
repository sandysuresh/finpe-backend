<?php

namespace App\Livewire\Admin;

use App\Models\CommissionEntry;
use App\Models\Vendor;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AgentCommission extends Component
{
    public string $vendorId = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('commission')) {
            abort(403);
        }
    }

    public function updated(string $name): void
    {
        if (in_array($name, ['vendorId', 'dateFrom', 'dateTo'], true) && ! AdminAccess::allows('commission', 'search')) {
            abort(403);
        }
    }

    public function render()
    {
        $rows = CommissionEntry::query()
            ->select('vendor_id', DB::raw('count(*) as entry_count'), DB::raw('sum(commission_amount) as total_commission'))
            ->whereNotNull('vendor_id')
            ->when($this->vendorId !== '', fn ($q) => $q->where('vendor_id', $this->vendorId))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->groupBy('vendor_id')
            ->orderByDesc('total_commission')
            ->get();

        if ($this->vendorId !== '' && $rows->isEmpty()) {
            $rows = collect([(object) [
                'vendor_id' => (int) $this->vendorId,
                'entry_count' => 0,
                'total_commission' => '0.00',
            ]]);
        }

        $names = Vendor::query()->whereIn('id', $rows->pluck('vendor_id'))->pluck('business_name', 'id');
        $entries = $this->vendorId === ''
            ? collect()
            : CommissionEntry::query()
                ->with('sourceTransaction:id,created_at')
                ->where('vendor_id', $this->vendorId)
                ->latest('id')
                ->limit(50)
                ->get();

        return view('livewire.admin.agent-commission', [
            'rows' => $rows,
            'names' => $names,
            'entries' => $entries,
            'vendors' => Vendor::query()->orderBy('business_name')->get(['id', 'business_name']),
        ])->layout('layouts.admin', ['title' => 'Agent Commission']);
    }
}
