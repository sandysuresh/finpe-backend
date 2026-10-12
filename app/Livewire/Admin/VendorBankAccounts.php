<?php

namespace App\Livewire\Admin;

use App\Models\VendorBankAccount;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class VendorBankAccounts extends Component
{
    use WithPagination;

    public string $filterStatus = 'pending';

    public string $search = '';

    public bool $showModal = false;

    public ?int $actionId = null;

    public string $actionType = '';

    public string $rejectionReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openAction(int $id, string $type): void
    {
        $needed = $type === 'approve' ? 'approve' : 'reject';
        if (! AdminAccess::allows('wallet-requests', $needed)) {
            return;
        }

        VendorBankAccount::query()->findOrFail($id);
        $this->actionId = $id;
        $this->actionType = $type;
        $this->rejectionReason = '';
        $this->showModal = true;
    }

    public function confirm(): void
    {
        $needed = $this->actionType === 'approve' ? 'approve' : 'reject';
        if (! AdminAccess::allows('wallet-requests', $needed)) {
            $this->showModal = false;

            return;
        }

        if ($this->actionType === 'reject') {
            $this->validate(['rejectionReason' => 'required|string|max:500']);
        }

        $account = VendorBankAccount::query()->findOrFail($this->actionId);
        if ($account->status !== 'pending') {
            $this->showModal = false;

            return;
        }

        if ($this->actionType === 'approve') {
            $account->update([
                'status' => 'approved',
                'rejection_reason' => null,
                'reviewed_by' => Auth::guard('admin')->id(),
                'reviewed_at' => now(),
            ]);
        } else {
            $account->update([
                'status' => 'rejected',
                'rejection_reason' => trim($this->rejectionReason),
                'reviewed_by' => Auth::guard('admin')->id(),
                'reviewed_at' => now(),
            ]);
        }

        $this->showModal = false;
        $this->reset(['actionId', 'actionType', 'rejectionReason']);
    }

    public function render()
    {
        $accounts = VendorBankAccount::query()
            ->with('vendor')
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('bank_name', 'like', $term)
                        ->orWhere('account_holder', 'like', $term)
                        ->orWhereHas('vendor', fn ($vendor) => $vendor
                            ->where('business_name', 'like', $term)
                            ->orWhere('vendor_code', 'like', $term));
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.vendor-bank-accounts', compact('accounts'))
            ->layout('layouts.admin');
    }
}
