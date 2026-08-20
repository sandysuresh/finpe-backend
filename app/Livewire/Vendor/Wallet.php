<?php
namespace App\Livewire\Vendor;

use App\Models\WalletTopupRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Wallet extends Component
{
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
    public string $bankName        = '';
    public string $remarks         = '';
    public string $successMsg      = '';
    public string $errorMsg        = '';

    // ── Tab: ledger | requests ────────────────────────────────────────────────
    public string $tab = 'ledger';

    public function mount(): void
    {
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
        $this->reset(['amount','paymentMode','transactionRef','bankName','remarks','successMsg','errorMsg']);
        $this->paymentMode = 'bank_transfer';
        $this->showModal   = true;
    }

    public function submitRequest(): void
    {
        $this->successMsg = '';
        $this->errorMsg   = '';

        $this->validate([
            'amount'        => 'required|numeric|min:1|max:10000000',
            'paymentMode'   => 'required|string',
            'transactionRef'=> 'nullable|string|max:100',
            'bankName'      => 'nullable|string|max:100',
            'remarks'       => 'nullable|string|max:500',
        ]);

        $vendor = Auth::guard('vendor')->user();

        WalletTopupRequest::create([
            'vendor_id'       => $vendor->id,
            'reference'       => 'TUP-' . strtoupper(Str::random(8)),
            'amount'          => $this->amount,
            'payment_mode'    => $this->paymentMode,
            'transaction_ref' => $this->transactionRef,
            'bank_name'       => $this->bankName,
            'remarks'         => $this->remarks,
            'status'          => 'pending',
        ]);

        $this->showModal  = false;
        $this->successMsg = 'Add money request submitted! Admin will review and credit your wallet shortly.';
        $this->tab        = 'requests';
        $this->reset(['amount','paymentMode','transactionRef','bankName','remarks']);
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
            $q = $wallet->ledger()->latest();
            if ($this->filterType) $q->where('type', $this->filterType);
            if ($this->filterFrom) $q->whereDate('created_at', '>=', $this->filterFrom);
            if ($this->filterTo)   $q->whereDate('created_at', '<=', $this->filterTo);
            $ledger = $q->paginate(15);
        }

        // Topup requests
        $topupRequests = $vendor->topupRequests()->latest()->paginate(10, ['*'], 'req_page');

        return view('livewire.vendor.wallet', compact('ledger','topupRequests'))
            ->layout('layouts.vendor', ['title' => 'Wallet']);
    }
}
