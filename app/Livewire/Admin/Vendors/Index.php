<?php

namespace App\Livewire\Admin\Vendors;

use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $kycStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingKycStatus(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $vendorId): void
    {
        $vendor = Vendor::findOrFail($vendorId);

        $vendor->update([
            'status' => $vendor->status === 'active'
                ? 'inactive'
                : 'active',
        ]);
    }

    public function render()
        {
            $vendors = Vendor::query()
                ->when($this->search !== '', function ($query) {
                    $query->where(function ($q) {
                        $q->where('vendor_code', 'like', '%' . $this->search . '%')
                            ->orWhere('business_name', 'like', '%' . $this->search . '%')
                            ->orWhere('contact_name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->status !== '', function ($query) {
                    $query->where('status', $this->status);
                })
                ->when($this->kycStatus !== '', function ($query) {
                    $query->where('kyc_status', $this->kycStatus);
                })
                ->latest()
                ->paginate(10);

            return view('livewire.admin.vendors.index', [
                'vendors' => $vendors,
            ])->layout('layouts.admin');
        }
}