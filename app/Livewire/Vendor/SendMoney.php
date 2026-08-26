<?php
namespace App\Livewire\Vendor;

use App\Models\Beneficiary;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class SendMoney extends Component {
    public string $step          = 'form'; // form | confirm | success
    public string $beneficiaryId = '';
    public string $accountNumber = '';
    public string $ifscCode      = '';
    public string $bankName      = '';
    public string $beneficiaryName = '';
    public string $amount        = '';
    public string $remarks       = '';
    public bool   $saveBeneficiary = false;
    public ?array $previewData   = null;
    public string $txReference   = '';

    public function getBeneficiariesProperty() {
        return Auth::guard('vendor')->user()
            ->beneficiaries()->where('status','active')->get();
    }

    public function fillBeneficiary(): void {
        if (!$this->beneficiaryId) { $this->reset(['accountNumber','ifscCode','bankName','beneficiaryName']); return; }
        $b = Auth::guard('vendor')->user()->beneficiaries()->find($this->beneficiaryId);
        if ($b) {
            $this->accountNumber   = $b->account_number;
            $this->ifscCode        = $b->ifsc_code ?? '';
            $this->bankName        = $b->bank_name ?? '';
            $this->beneficiaryName = $b->name;
        }
    }

    public function preview(): void {
        $this->validate([
            'accountNumber'   => 'required|string|min:8',
            'beneficiaryName' => 'required|string',
            'amount'          => 'required|numeric|min:1',
        ]);
        $vendor = Auth::guard('vendor')->user();
        $balance = (float)optional($vendor->wallet)->balance;
        if ((float)$this->amount > $balance) {
            $this->addError('amount', 'Insufficient balance. Available: ₹'.number_format($balance,2));
            return;
        }
        $this->previewData = [
            'beneficiary_name' => $this->beneficiaryName,
            'account_number'   => $this->accountNumber,
            'ifsc_code'        => $this->ifscCode,
            'bank_name'        => $this->bankName,
            'amount'           => number_format((float)$this->amount, 2),
            'remarks'          => $this->remarks,
        ];
        $this->step = 'confirm';
    }

    public function submit(): void {
        $vendor = Auth::guard('vendor')->user();
        $this->txReference = 'TXN-'.strtoupper(Str::random(10));

        Transaction::create([
            'vendor_id'        => $vendor->id,
            'reference'        => $this->txReference,
            'amount'           => $this->amount,
            'type'             => 'payout',
            'service'          => 'imps',
            'beneficiary_name' => $this->beneficiaryName,
            'status'           => 'pending',
        ]);

        if ($this->saveBeneficiary && !$this->beneficiaryId) {
            Beneficiary::firstOrCreate(
                ['vendor_id'=>$vendor->id,'account_number'=>$this->accountNumber],
                ['name'=>$this->beneficiaryName,'ifsc_code'=>$this->ifscCode,'bank_name'=>$this->bankName]
            );
        }

        $this->step = 'success';
    }

    public function newTransaction(): void {
        $this->reset();
        $this->step = 'form';
    }

    public function render() {
        return view('livewire.vendor.send-money')
            ->layout('layouts.vendor', ['title' => 'Send Money']);
    }
}
