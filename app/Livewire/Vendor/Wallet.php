<?php
namespace App\Livewire\Vendor;

use App\Models\VendorBankAccount;
use App\Models\WalletTopupRequest;
use App\Support\AdminNotify;
use App\Support\AssignedPayoutProviders;
use App\Support\VisibleTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Wallet extends Component
{
    use WithFileUploads;
    use WithPagination;

    // ── Balances ──────────────────────────────────────────────────────────────
    public string $availableBalance = '0.00';
    public string $holdBalance      = '0.00';

    // ── Ledger filters ────────────────────────────────────────────────────────
    public string $filterType = '';
    public string $filterFrom = '';
    public string $filterTo   = '';

    // ── Add Money modal ───────────────────────────────────────────────────────
    public bool   $showModal       = false;
    public string $amount          = '';
    public string $paymentMode     = 'bank_transfer';
    public string $transactionRef  = '';
    public string $bankAccountId  = '';
    public $receipt                = null;
    public string $remarks         = '';
    public string $successMsg      = '';
    public string $errorMsg        = '';

    // ── Tab: ledger | requests ────────────────────────────────────────────────
    public string $tab = 'ledger';

    public function mount(): void
    {
        if (request()->query('tab') === 'requests') {
            $this->tab = 'requests';
        }
        $this->refreshBalances();
    }

    private function refreshBalances(): void
    {
        $wallet = Auth::guard('vendor')->user()->wallet;
        if ($wallet) {
            $this->availableBalance = number_format((float) $wallet->balance, 2);
            $this->holdBalance      = number_format((float) $wallet->hold_balance, 2);
        }
    }

    public function openModal(): void
    {
        $this->reset(['amount','paymentMode','transactionRef','bankAccountId','receipt','remarks','successMsg','errorMsg']);
        $this->paymentMode = 'bank_transfer';
        $this->showModal   = true;
    }

    public function submitRequest(): void
    {
        $this->successMsg = '';
        $this->errorMsg   = '';

        $this->validate([
            'amount'         => 'required|numeric|min:1|max:10000000',
            'paymentMode'    => 'required|string',
            'transactionRef' => 'nullable|string|max:100',
            'bankAccountId'  => 'required|integer',
            'receipt'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'remarks'        => 'nullable|string|max:500',
        ]);

        $vendor = Auth::guard('vendor')->user();
        $account = VendorBankAccount::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved')
            ->whereKey((int) $this->bankAccountId)
            ->first();

        if (! $account) {
            $this->addError('bankAccountId', 'Select one of your approved bank accounts.');

            return;
        }

        $reference = 'TUP-'.strtoupper(Str::random(8));
        $receiptPath = $this->receipt
            ? $this->receipt->store('wallet-receipts/'.$vendor->id, 'local')
            : null;

        $request = WalletTopupRequest::create([
            'vendor_id'              => $vendor->id,
            'reference'              => $reference,
            'amount'                 => $this->amount,
            'payment_mode'           => $this->paymentMode,
            'transaction_ref'        => $this->transactionRef,
            'bank_name'              => $account->label(),
            'vendor_bank_account_id' => $account->id,
            'receipt_path'           => $receiptPath,
            'remarks'                => $this->remarks,
            'status'                 => 'pending',
        ]);

        AdminNotify::walletTopup($vendor, $request->reference, (string) $request->amount, $request->id);

        $this->showModal  = false;
        $this->successMsg = 'Add money request submitted! Admin will review and credit your wallet shortly.';
        $this->tab        = 'requests';
        $this->reset(['amount','paymentMode','transactionRef','bankAccountId','receipt','remarks']);
    }

    public function resetFilters(): void
    {
        $this->filterType = '';
        $this->filterFrom = '';
        $this->filterTo   = '';
        $this->resetPage();
    }

    public function render()
    {
        $vendor = Auth::guard('vendor')->user();
        $wallet = $vendor->wallet;

        // Ledger
        $ledger = null;
        if ($wallet) {
            $q = $wallet->ledger();
            AssignedPayoutProviders::restrictLedger($q, $vendor);
            $q->latest();
            if ($this->filterType) $q->where('type', $this->filterType);
            if ($this->filterFrom) $q->whereDate('created_at', '>=', $this->filterFrom);
            if ($this->filterTo)   $q->whereDate('created_at', '<=', $this->filterTo);
            $ledger = $q->paginate(15);
        }

        $payoutByReference = collect();
        if ($ledger && $ledger->isNotEmpty()) {
            $refs = $ledger->getCollection()->pluck('reference')->filter()->unique()->values();
            if ($refs->isNotEmpty()) {
                $payoutByReference = app(VisibleTransactions::class)->payoutsByReference((int) $vendor->id, $refs, AssignedPayoutProviders::codes($vendor));
            }
        }

        // Topup requests
        $topupRequests = $vendor->topupRequests()->with('vendorBankAccount')->latest()->paginate(10, ['*'], 'req_page');
        $approvedBanks = $vendor->bankAccounts()->where('status', 'approved')->orderBy('bank_name')->get();

        return view('livewire.vendor.wallet', compact('ledger','topupRequests','payoutByReference','approvedBanks'))
            ->layout('layouts.vendor', ['title' => 'Wallet']);
    }
}
