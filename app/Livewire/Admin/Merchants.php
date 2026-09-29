<?php

namespace App\Livewire\Admin;

use App\Models\CommissionEntry;
use App\Models\Merchant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Merchants extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('vendors')) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $merchants = Merchant::query()
            ->with('vendor:id,business_name,vendor_code')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('code', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhereHas('vendor', fn ($vendor) => $vendor->where('business_name', 'like', $term));
                });
            })
            ->orderByDesc('id')
            ->paginate(20);

        $applied = CommissionEntry::query()
            ->select('merchant_id', DB::raw('count(*) as entry_count'), DB::raw('sum(commission_amount) as total_commission'))
            ->whereIn('merchant_id', $merchants->pluck('id'))
            ->groupBy('merchant_id')
            ->get()
            ->keyBy('merchant_id');

        return view('livewire.admin.merchants', [
            'merchants' => $merchants,
            'applied' => $applied,
        ])->layout('layouts.admin', ['title' => 'Merchants']);
    }
}
