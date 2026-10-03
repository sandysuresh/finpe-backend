<?php

namespace App\Livewire\Admin;

use App\Models\CommissionEntry;
use App\Models\CommissionRule;
use App\Models\Settlement;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Services\CommissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PartnerSection extends Component
{
    use WithPagination;

    public string $section = 'kyc';

    public string $search = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('vendors')) {
            abort(403);
        }

        $section = match (request()->route()?->getName()) {
            'admin.partners.wallet' => 'wallet',
            'admin.partners.transactions' => 'transactions',
            'admin.partners.commission' => 'commission',
            'admin.partners.settlement' => 'settlement',
            default => 'kyc',
        };

        $this->section = $section;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $meta = $this->meta()[$this->section];

        $rows = $this->rows();
        $applied = collect();
        $configured = collect();
        if ($this->section === 'commission') {
            $applied = CommissionEntry::query()
                ->select('vendor_id', DB::raw('count(*) as entry_count'), DB::raw('sum(commission_amount) as total_commission'))
                ->where('status', CommissionEntry::STATUS_RECORDED)
                ->whereIn('vendor_id', $rows->pluck('id'))
                ->groupBy('vendor_id')
                ->get()
                ->keyBy('vendor_id');

            $rules = CommissionRule::query()->where('status', 'active')->get();
            $selector = app(CommissionService::class);
            $configured = $rows->getCollection()->mapWithKeys(fn (Vendor $vendor) => [
                $vendor->id => $selector->configuredPayoutRule($vendor->id, now(), $rules),
            ]);
        }

        return view('livewire.admin.partner-section', [
            'meta' => $meta,
            'rows' => $rows,
            'applied' => $applied,
            'configured' => $configured,
        ])->layout('layouts.admin', ['title' => $meta['title']]);
    }

    private function rows()
    {
        $term = '%'.$this->search.'%';

        return match ($this->section) {
            'wallet' => Vendor::query()->with('wallet')
                ->when($this->search !== '', function ($q) use ($term) {
                    $q->where(function ($inner) use ($term) {
                        $inner->where('business_name', 'like', $term)->orWhere('vendor_code', 'like', $term);
                    });
                })
                ->latest()->paginate(15),
            'transactions' => Transaction::query()->with(['vendor', 'commissionEntry'])
                ->when($this->search !== '', function ($q) use ($term) {
                    $q->where(function ($inner) use ($term) {
                        $inner->where('reference', 'like', $term)
                            ->orWhereHas('vendor', fn ($vendor) => $vendor->where('business_name', 'like', $term));
                    });
                })
                ->latest()->paginate(15),
            'settlement' => Settlement::query()->with('vendor')
                ->when($this->search !== '', function ($q) use ($term) {
                    $q->where(function ($inner) use ($term) {
                        $inner->where('reference', 'like', $term)
                            ->orWhereHas('vendor', fn ($vendor) => $vendor->where('business_name', 'like', $term));
                    });
                })
                ->latest()->paginate(15),
            default => Vendor::query()
                ->when($this->search !== '', function ($q) use ($term) {
                    $q->where(function ($inner) use ($term) {
                        $inner->where('business_name', 'like', $term)->orWhere('vendor_code', 'like', $term);
                    });
                })
                ->latest()->paginate(15),
        };
    }

    private function meta(): array
    {
        return [
            'kyc' => ['title' => 'Partner KYC', 'text' => 'KYC status for each partner.'],
            'wallet' => ['title' => 'Partner Wallet', 'text' => 'Current balance and hold for each partner.'],
            'transactions' => ['title' => 'Partner Transactions', 'text' => 'Payout transactions raised by partners.'],
            'commission' => ['title' => 'Partner Commission', 'text' => 'Configured rule is the active commission rule. Applied commission is the recorded commission entries.'],
            'settlement' => ['title' => 'Partner Settlement', 'text' => 'Settlement records for partners.'],
        ];
    }
}
