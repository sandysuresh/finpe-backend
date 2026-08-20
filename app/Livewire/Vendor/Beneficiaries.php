<?php
namespace App\Livewire\Vendor;

use App\Models\Beneficiary;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Beneficiaries extends Component {
    use WithPagination;

    public bool   $showModal   = false;
    public bool   $editMode    = false;
    public ?int   $editId      = null;
    public string $search      = '';
    public string $name        = '';
    public string $accountNumber = '';
    public string $ifscCode    = '';
    public string $bankName    = '';
    public string $mobile      = '';
    public string $email       = '';

    public function openCreate(): void {
        $this->reset(['name','accountNumber','ifscCode','bankName','mobile','email','editId']);
        $this->editMode  = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void {
        $b = Beneficiary::findOrFail($id);
        $this->editId        = $id;
        $this->name          = $b->name;
        $this->accountNumber = $b->account_number;
        $this->ifscCode      = $b->ifsc_code ?? '';
        $this->bankName      = $b->bank_name ?? '';
        $this->mobile        = $b->mobile ?? '';
        $this->email         = $b->email  ?? '';
        $this->editMode      = true;
        $this->showModal     = true;
    }

    public function save(): void {
        $this->validate([
            'name'          => 'required|string|max:100',
            'accountNumber' => 'required|string|min:8|max:20',
            'ifscCode'      => 'nullable|string|max:15',
            'bankName'      => 'nullable|string|max:100',
            'mobile'        => 'nullable|string|max:15',
            'email'         => 'nullable|email',
        ]);
        $vendor = Auth::guard('vendor')->user();
        $data   = [
            'name'=>$this->name,'account_number'=>$this->accountNumber,
            'ifsc_code'=>$this->ifscCode,'bank_name'=>$this->bankName,
            'mobile'=>$this->mobile,'email'=>$this->email,
        ];
        if ($this->editMode) {
            Beneficiary::where('id',$this->editId)->where('vendor_id',$vendor->id)->update($data);
        } else {
            $data['vendor_id'] = $vendor->id;
            Beneficiary::create($data);
        }
        $this->showModal = false;
        $this->reset(['name','accountNumber','ifscCode','bankName','mobile','email']);
        $this->dispatch('notify', message: $this->editMode ? 'Beneficiary updated.' : 'Beneficiary added.');
    }

    public function delete(int $id): void {
        Beneficiary::where('id',$id)->where('vendor_id',Auth::guard('vendor')->id())->delete();
    }

    public function render() {
        $vendor = Auth::guard('vendor')->user();
        $beneficiaries = $vendor->beneficiaries()
            ->when($this->search, fn($q) => $q->where('name','like',"%{$this->search}%")
                ->orWhere('account_number','like',"%{$this->search}%"))
            ->latest()->paginate(12);
        return view('livewire.vendor.beneficiaries', compact('beneficiaries'))
            ->layout('layouts.vendor', ['title' => 'Beneficiaries']);
    }
}
