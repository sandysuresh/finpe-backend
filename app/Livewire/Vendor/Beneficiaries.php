<?php
namespace App\Livewire\Vendor;

use App\Models\Beneficiary;
use App\Models\Transaction;
use App\Support\CommissionProviders;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void {
        $this->reset(['name','accountNumber','ifscCode','bankName','mobile','email','editId']);
        $this->editMode  = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void {
        $b = Auth::guard('vendor')->user()->beneficiaries()->findOrFail($id);
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

    public function maskAccount(?string $account): string
    {
        $account = (string) $account;
        if ($account === '') {
            return '—';
        }
        if (strlen($account) <= 4) {
            return str_repeat('X', strlen($account));
        }

        return str_repeat('X', strlen($account) - 4).substr($account, -4);
    }

    public function maskMobile(?string $mobile): string
    {
        $mobile = preg_replace('/\D+/', '', (string) $mobile) ?? '';
        if ($mobile === '') {
            return '—';
        }
        if (strlen($mobile) <= 4) {
            return str_repeat('X', strlen($mobile));
        }

        return str_repeat('X', strlen($mobile) - 4).substr($mobile, -4);
    }

    public function render() {
        $vendorId = (int) Auth::guard('vendor')->id();
        $rows = $this->payoutBeneficiaries($vendorId);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $beneficiaries = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('livewire.vendor.beneficiaries', compact('beneficiaries'))
            ->layout('layouts.vendor', ['title' => 'Beneficiaries']);
    }

    private function payoutBeneficiaries(int $vendorId)
    {
        $saved = Beneficiary::query()
            ->where('vendor_id', $vendorId)
            ->orderByDesc('id')
            ->get()
            ->keyBy(fn (Beneficiary $row) => $this->beneficiaryKey($row->account_number, $row->ifsc_code));

        $transactions = Transaction::query()
            ->where('vendor_id', $vendorId)
            ->where('type', 'payout')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get([
                'beneficiary_name', 'account_number', 'ifsc_code', 'bank_name',
                'beneficiary_bank_code', 'beneficiary_mobile', 'payout_provider',
                'status', 'created_at',
            ]);

        $unique = [];
        foreach ($transactions as $txn) {
            $key = $this->beneficiaryKey($txn->account_number, $txn->ifsc_code);
            if ($key === '|' || isset($unique[$key])) {
                continue;
            }

            $bank = $this->bankLabel($txn->bank_name, $txn->beneficiary_bank_code);
            $savedRow = $saved->get($key);
            $unique[$key] = [
                'beneficiary_id' => $savedRow?->id,
                'name' => $txn->beneficiary_name ?: '—',
                'account' => $this->maskAccount($txn->account_number),
                'account_search' => (string) $txn->account_number,
                'ifsc' => $txn->ifsc_code ?: '—',
                'bank' => $bank !== '' ? $bank : '—',
                'mobile' => $this->maskMobile($txn->beneficiary_mobile),
                'mobile_search' => (string) $txn->beneficiary_mobile,
                'provider' => CommissionProviders::name($txn->payout_provider) ?: '—',
                'last_at' => $txn->created_at?->format('d M Y, h:i A') ?: '—',
                'status' => $txn->status ?: null,
            ];
        }

        $term = mb_strtolower(trim($this->search));
        $rows = collect($unique)->values();
        if ($term !== '') {
            $rows = $rows->filter(function (array $row) use ($term) {
                $haystack = mb_strtolower(implode(' ', [
                    $row['name'], $row['account_search'], $row['ifsc'], $row['bank'], $row['mobile_search'], $row['provider'],
                ]));

                return str_contains($haystack, $term);
            })->values();
        }

        return $rows->map(function (array $row) {
            unset($row['account_search'], $row['mobile_search']);

            return $row;
        });
    }

    private function beneficiaryKey(?string $account, ?string $ifsc): string
    {
        return strtoupper(trim((string) $account)).'|'.strtoupper(trim((string) $ifsc));
    }

    private function bankLabel(?string $name, ?string $code): string
    {
        $name = trim((string) $name);
        $code = trim((string) $code);
        if ($name !== '' && $code !== '' && strcasecmp($name, $code) !== 0) {
            return $name.' · '.$code;
        }

        return $name !== '' ? $name : $code;
    }
}
