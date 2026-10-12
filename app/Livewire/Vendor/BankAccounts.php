<?php

namespace App\Livewire\Vendor;

use App\Models\VendorBankAccount;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BankAccounts extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $editMode = false;

    public ?int $editId = null;

    public string $accountHolder = '';

    public string $accountNumber = '';

    public string $ifscCode = '';

    public string $bankName = '';

    public string $successMsg = '';

    public function openCreate(): void
    {
        $this->reset(['accountHolder', 'accountNumber', 'ifscCode', 'bankName', 'editId']);
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $account = $this->owned($id);
        $this->editId = $account->id;
        $this->accountHolder = $account->account_holder;
        $this->accountNumber = $account->account_number;
        $this->ifscCode = $account->ifsc_code;
        $this->bankName = $account->bank_name;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->successMsg = '';
        $this->validate([
            'accountHolder' => 'required|string|max:120',
            'accountNumber' => 'required|digits_between:9,18',
            'ifscCode' => ['required', 'string', 'regex:/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/'],
            'bankName' => 'required|string|max:120',
        ]);

        $vendorId = (int) Auth::guard('vendor')->id();
        $data = [
            'account_holder' => trim($this->accountHolder),
            'account_number' => preg_replace('/\s+/', '', $this->accountNumber),
            'ifsc_code' => strtoupper(trim($this->ifscCode)),
            'bank_name' => trim($this->bankName),
            'status' => 'pending',
            'rejection_reason' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];

        if ($this->editMode) {
            $this->owned((int) $this->editId)->update($data);
            $this->successMsg = 'Bank account updated and sent for verification.';
        } else {
            VendorBankAccount::query()->create($data + ['vendor_id' => $vendorId]);
            $this->successMsg = 'Bank account submitted for verification.';
        }

        $this->showModal = false;
        $this->reset(['accountHolder', 'accountNumber', 'ifscCode', 'bankName', 'editId']);
        $this->editMode = false;
    }

    public function render()
    {
        $accounts = VendorBankAccount::query()
            ->where('vendor_id', Auth::guard('vendor')->id())
            ->latest()
            ->paginate(10);

        return view('livewire.vendor.bank-accounts', compact('accounts'))
            ->layout('layouts.vendor', ['title' => 'Bank Accounts']);
    }

    private function owned(int $id): VendorBankAccount
    {
        return VendorBankAccount::query()
            ->where('vendor_id', Auth::guard('vendor')->id())
            ->findOrFail($id);
    }
}
