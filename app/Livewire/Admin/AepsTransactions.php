<?php

namespace App\Livewire\Admin;

use App\Models\AepsTransaction;
use App\Models\Merchant;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AepsTransactions extends Component
{
    use WithPagination;

    public string $vendorId = '';

    public string $merchantId = '';

    public string $service = '';

    public string $status = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public ?int $detailId = null;

    public function mount(?string $reference = null): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('transactions')) {
            abort(403);
        }

        if ($reference) {
            $id = AepsTransaction::query()->where('reference', $reference)->value('id');
            if (! $id) {
                abort(404);
            }
            $this->detailId = (int) $id;
        }
    }

    public function show(int $id): void
    {
        $exists = AepsTransaction::query()->whereKey($id)->exists();
        if (! $exists) {
            return;
        }

        $this->detailId = $id;
    }

    public static function serviceLabel(string $service): string
    {
        return match ($service) {
            'CW' => 'Cash Withdrawal (CW)',
            'BE' => 'Balance Enquiry (BE)',
            'MS' => 'Mini Statement (MS)',
            'AP' => 'Aadhaar Pay (AP)',
            'CD' => 'Cash Deposit (CD)',
            'CWTFA' => 'Cash Withdrawal OTP (CWTFA)',
            'APTFA' => 'Aadhaar Pay OTP (APTFA)',
            default => $service,
        };
    }

    public function updatingVendorId(): void
    {
        $this->merchantId = '';
        $this->resetPage();
    }

    public function updatingMerchantId(): void
    {
        $this->resetPage();
    }

    public function updatingService(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $detail = null;
        if ($this->detailId) {
            $detail = AepsTransaction::query()
                ->with(['vendor:id,business_name,vendor_code', 'merchant:id,code,first_name,last_name'])
                ->find($this->detailId);
        }

        $transactions = AepsTransaction::query()
            ->with(['vendor:id,business_name,vendor_code', 'merchant:id,code,first_name,last_name'])
            ->when($this->vendorId !== '', fn ($query) => $query->where('vendor_id', $this->vendorId))
            ->when($this->merchantId !== '', fn ($query) => $query->where('merchant_id', $this->merchantId))
            ->when($this->service !== '', fn ($query) => $query->where('service', $this->service))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('id')
            ->paginate(20);

        return view('livewire.admin.aeps-transactions', [
            'transactions' => $transactions,
            'detail' => $detail,
            'vendors' => Vendor::query()->orderBy('business_name')->get(['id', 'business_name']),
            'merchants' => Merchant::query()
                ->when($this->vendorId !== '', fn ($query) => $query->where('vendor_id', $this->vendorId))
                ->orderBy('code')
                ->get(['id', 'code', 'first_name', 'last_name', 'vendor_id']),
            'services' => ['CW', 'BE', 'MS', 'AP', 'CD', 'CWTFA', 'APTFA'],
            'statuses' => ['initiated', 'processing', 'success', 'pending', 'failed', 'validation_failed', 'provider_error'],
        ])->layout('layouts.admin', ['title' => 'AePS Transactions']);
    }
}
