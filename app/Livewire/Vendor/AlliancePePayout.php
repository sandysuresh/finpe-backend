<?php

namespace App\Livewire\Vendor;

use App\Exceptions\AlliancePeException;
use App\Models\AlliancePePayout as AlliancePePayoutModel;
use App\Models\Vendor;
use App\Models\WalletLedger;
use App\Services\AlliancePe\AlliancePePayoutService;
use App\Support\AssignedPayoutProviders;
use App\Support\CommissionProviders;
use Livewire\Component;
use Livewire\WithPagination;

class AlliancePePayout extends Component
{
    use WithPagination;

    public string $amount = '';

    public string $beneficiaryName = '';

    public string $bankName = '';

    public string $accountNumber = '';

    public string $confirmAccountNumber = '';

    public string $ifsc = '';

    public string $phone = '';

    public string $email = '';

    public string $transferMode = 'IMPS';

    public string $remarks = '';

    public bool $confirming = false;

    public bool $submitting = false;

    public ?string $merchantOrderId = null;

    public ?string $error = null;

    public ?string $notice = null;

    public string $search = '';

    public string $status = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public ?int $selectedId = null;

    public function mount(): void
    {
        AssignedPayoutProviders::assertAllows($this->vendor(), CommissionProviders::ALLIANCEPE);
        $id = (int) request()->query('transaction', 0);
        if ($id > 0) {
            $this->show($id);
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }

    public function review(): void
    {
        AssignedPayoutProviders::assertAllows($this->vendor(), CommissionProviders::ALLIANCEPE);
        $this->error = $this->validateForm();
        if ($this->error !== null) {
            return;
        }
        $this->merchantOrderId = $this->merchantOrderId ?: 'AP-'.auth('vendor')->id().'-'.now()->format('YmdHis').'-'.strtolower(\Illuminate\Support\Str::random(6));
        $this->confirming = true;
    }

    public function cancelReview(): void
    {
        if ($this->submitting) {
            return;
        }
        $this->confirming = false;
    }

    public function show(int $id): void
    {
        $vendor = $this->vendor();
        AssignedPayoutProviders::assertAllows($vendor, CommissionProviders::ALLIANCEPE);
        $this->selectedId = AlliancePePayoutModel::query()
            ->where('vendor_id', $vendor->id)
            ->whereKey($id)
            ->value('id');
    }

    public function submit(AlliancePePayoutService $payouts): void
    {
        if ($this->submitting || ! $this->confirming) {
            return;
        }
        $this->submitting = true;
        $this->error = $this->validateForm();
        if ($this->error !== null) {
            $this->submitting = false;

            return;
        }
        $vendor = $this->vendor();
        AssignedPayoutProviders::assertAllows($vendor, CommissionProviders::ALLIANCEPE);
        try {
            $payout = $payouts->submit($vendor, $this->input(), $this->merchantOrderId);
            $this->notice = 'Payout accepted for processing. Reference '.$payout->merchant_order_id.'. Status: '.$payout->status.'.';
            $this->confirming = false;
            $this->resetForm();
        } catch (AlliancePeException $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->submitting = false;
        }
    }

    public function render()
    {
        $vendor = $this->vendor();
        AssignedPayoutProviders::assertAllows($vendor, CommissionProviders::ALLIANCEPE);
        $vendor->loadMissing('wallet');
        $selected = $this->selected($vendor);

        return view('livewire.vendor.alliancepe-payout', [
            'balance' => $vendor->wallet ? (float) $vendor->wallet->balance : 0,
            'balanceAlert' => $this->balanceAlert($vendor),
            'payouts' => AlliancePePayoutModel::query()->history([
                'vendor_id' => $vendor->id,
                'search' => $this->search,
                'status' => $this->status,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
            ])->latest('id')->paginate(15),
            'selected' => $selected,
            'timeline' => $selected ? $selected->events()->orderBy('id')->get() : collect(),
            'ledger' => $selected ? WalletLedger::query()
                ->where('vendor_id', $vendor->id)
                ->where('reference', $selected->merchant_order_id)
                ->where('source', 'alliancepe')
                ->orderBy('id')
                ->get() : collect(),
        ])->layout('layouts.vendor', ['title' => 'AlliancePe Payout']);
    }

    /**
     * @return array{amount: float, beneficiary_name: string, bank_name: string, account_number: string, ifsc: string, phone: string, email: string, transfer_mode: string, remarks: ?string}
     */
    private function input(): array
    {
        return [
            'amount' => round((float) $this->amount, 2),
            'beneficiary_name' => trim($this->beneficiaryName),
            'bank_name' => trim($this->bankName),
            'account_number' => preg_replace('/\D/', '', $this->accountNumber) ?? '',
            'ifsc' => strtoupper(trim($this->ifsc)),
            'phone' => trim($this->phone),
            'email' => trim($this->email),
            'transfer_mode' => strtolower($this->transferMode),
            'remarks' => trim($this->remarks) !== '' ? trim($this->remarks) : null,
        ];
    }

    private function validateForm(): ?string
    {
        $input = $this->input();
        if ($input['beneficiary_name'] === '' || mb_strlen($input['beneficiary_name']) > 100) {
            return 'Beneficiary name is required and must be 100 characters or less.';
        }
        if ($input['bank_name'] === '') {
            return 'Bank name is required.';
        }
        if (preg_match('/^\d{9,18}$/', $input['account_number']) !== 1) {
            return 'Account number must be 9 to 18 digits.';
        }
        if ($input['account_number'] !== preg_replace('/\D/', '', $this->confirmAccountNumber)) {
            return 'Account numbers do not match.';
        }
        if (preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $input['ifsc']) !== 1) {
            return 'IFSC must be 4 letters, 0, then 6 letters or digits.';
        }
        if (preg_match('/^\d{10}$/', $input['phone']) !== 1) {
            return 'Phone number must be 10 digits.';
        }
        if (filter_var($input['email'], FILTER_VALIDATE_EMAIL) === false) {
            return 'A valid beneficiary email is required.';
        }
        if (! in_array($input['transfer_mode'], ['imps', 'neft', 'rtgs'], true)) {
            return 'Transfer mode must be IMPS, NEFT or RTGS.';
        }
        if ($input['amount'] < 100 || $input['amount'] > 100000) {
            return 'Amount must be from ₹100 to ₹1,00,000.';
        }

        return null;
    }

    private function resetForm(): void
    {
        $this->amount = '';
        $this->beneficiaryName = '';
        $this->bankName = '';
        $this->accountNumber = '';
        $this->confirmAccountNumber = '';
        $this->ifsc = '';
        $this->phone = '';
        $this->email = '';
        $this->transferMode = 'IMPS';
        $this->remarks = '';
        $this->merchantOrderId = null;
    }

    private function balanceAlert(Vendor $vendor): ?string
    {
        if (! is_string($this->error) || ! str_contains($this->error, 'Insufficient')) {
            return null;
        }
        $attempt = $this->merchantOrderId
            ? AlliancePePayoutModel::query()
                ->where('vendor_id', $vendor->id)
                ->where('merchant_order_id', $this->merchantOrderId)
                ->first()
            : null;
        $amount = $attempt ? (float) $attempt->amount : (float) $this->amount;
        $commission = $attempt ? (float) $attempt->commission_amount : 0.0;
        $available = $vendor->wallet ? (float) $vendor->wallet->balance : 0.0;

        return 'Insufficient wallet balance. This payout debits ₹'
            .number_format(round($amount + $commission, 2), 2)
            .' (₹'.number_format($amount, 2).' amount + ₹'.number_format($commission, 2)
            .' FinPe commission). Available: ₹'.number_format($available, 2).'.';
    }

    private function vendor(): Vendor
    {
        $vendor = auth('vendor')->user();
        abort_unless($vendor instanceof Vendor, 403);

        return $vendor;
    }

    private function selected(Vendor $vendor): ?AlliancePePayoutModel
    {
        if (! $this->selectedId) {
            return null;
        }

        return AlliancePePayoutModel::query()
            ->where('vendor_id', $vendor->id)
            ->find($this->selectedId);
    }
}
