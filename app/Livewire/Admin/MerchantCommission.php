<?php

namespace App\Livewire\Admin;

use App\Models\CommissionEntry;
use App\Models\Merchant;
use App\Models\Vendor;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MerchantCommission extends Component
{
    public string $vendorId = '';
    public string $merchantId = '';
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
        if (in_array($name, ['vendorId', 'merchantId', 'dateFrom', 'dateTo'], true) && ! AdminAccess::allows('commission', 'search')) {
            abort(403);
        }
    }

    public function render()
    {
        $rows = CommissionEntry::query()
            ->select('merchant_id', 'vendor_id', DB::raw('count(*) as entry_count'), DB::raw('sum(commission_amount) as total_commission'))
            ->whereNotNull('merchant_id')
            ->when($this->vendorId !== '', fn ($q) => $q->where('vendor_id', $this->vendorId))
            ->when($this->merchantId !== '', fn ($q) => $q->where('merchant_id', $this->merchantId))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->groupBy('merchant_id', 'vendor_id')
            ->orderByDesc('total_commission')
            ->get();

        if ($this->merchantId !== '' && $rows->isEmpty()) {
            $merchant = Merchant::query()->find($this->merchantId);
            if ($merchant && ($this->vendorId === '' || (string) $merchant->vendor_id === $this->vendorId)) {
                $rows = collect([(object) [
                    'merchant_id' => $merchant->id,
                    'vendor_id' => $merchant->vendor_id,
                    'entry_count' => 0,
                    'total_commission' => '0.00',
                ]]);
            }
        }

        $merchants = Merchant::query()->whereIn('id', $rows->pluck('merchant_id'))->get()->keyBy('id');
        $vendors = Vendor::query()->whereIn('id', $rows->pluck('vendor_id'))->pluck('business_name', 'id');
        $entries = $this->merchantId === ''
            ? collect()
            : CommissionEntry::query()
                ->with(['sourceTransaction:id,created_at', 'merchant:id,code,first_name,last_name'])
                ->where('merchant_id', $this->merchantId)
                ->when($this->vendorId !== '', fn ($q) => $q->where('vendor_id', $this->vendorId))
                ->latest('id')
                ->limit(50)
                ->get();

        return view('livewire.admin.merchant-commission', [
            'rows' => $rows,
            'merchants' => $merchants,
            'vendorNames' => $vendors,
            'entries' => $entries,
            'vendors' => Vendor::query()->orderBy('business_name')->get(['id', 'business_name']),
            'merchantOptions' => Merchant::query()->orderBy('code')->get(['id', 'code', 'vendor_id']),
        ])->layout('layouts.admin', ['title' => 'Merchant Commission']);
    }
}
