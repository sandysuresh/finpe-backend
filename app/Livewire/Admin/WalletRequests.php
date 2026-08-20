<?php
namespace App\Livewire\Admin;

use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Models\WalletTopupRequest;
use App\Support\UrlId;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class WalletRequests extends Component
{
    use WithPagination;

    public string $filterStatus = '';
    public string $search       = '';
    public ?int $highlightId    = null;

    // Approve modal
    public bool   $showApproveModal = false;
    public ?int   $actionId         = null;
    public string $adminNote        = '';
    public string $actionType       = ''; // approve | reject

    public function mount(): void
    {
        $this->highlightId = UrlId::decode(request()->query('request'));
    }

    public function openAction(int $id, string $type): void
    {
        $this->actionId         = $id;
        $this->actionType       = $type;
        $this->adminNote        = '';
        $this->showApproveModal = true;
    }

    public function confirm(): void
    {
        $request = WalletTopupRequest::with('vendor')->findOrFail($this->actionId);

        if ($request->status !== 'pending') {
            $this->showApproveModal = false;
            return;
        }

        DB::transaction(function () use ($request) {
            if ($this->actionType === 'approve') {
                // Get or create wallet
                $wallet = $request->vendor->wallet ?? Wallet::create([
                    'vendor_id'    => $request->vendor_id,
                    'balance'      => 0,
                    'hold_balance' => 0,
                ]);

                $balanceBefore = (float) $wallet->balance;
                $balanceAfter  = $balanceBefore + (float) $request->amount;

                // Credit wallet
                $wallet->increment('balance', (float) $request->amount);

                // Write ledger entry
                WalletLedger::create([
                    'vendor_id'      => $request->vendor_id,
                    'wallet_id'      => $wallet->id,
                    'type'           => 'credit',
                    'amount'         => $request->amount,
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $balanceAfter,
                    'reference'      => $request->reference,
                    'description'    => 'Wallet top-up approved by admin',
                    'source'         => 'topup',
                ]);

                $request->update([
                    'status'      => 'approved',
                    'admin_note'  => $this->adminNote,
                    'approved_by' => Auth::guard('admin')->id(),
                    'actioned_at' => now(),
                ]);
            } else {
                $request->update([
                    'status'      => 'rejected',
                    'admin_note'  => $this->adminNote,
                    'approved_by' => Auth::guard('admin')->id(),
                    'actioned_at' => now(),
                ]);
            }
        });

        $this->showApproveModal = false;
        $this->resetPage();
    }

    public function updatingSearch(): void  { $this->resetPage(); }

    public function render()
    {
        $requests = WalletTopupRequest::with('vendor','approvedBy')
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, fn($q) => $q->whereHas('vendor', fn($vq) =>
                $vq->where('business_name','like',"%{$this->search}%")
                   ->orWhere('vendor_code','like',"%{$this->search}%")
            ))
            ->latest()
            ->paginate(15);

        $summary = [
            'pending'  => WalletTopupRequest::where('status','pending')->count(),
            'approved' => WalletTopupRequest::where('status','approved')->count(),
            'rejected' => WalletTopupRequest::where('status','rejected')->count(),
            'total_credited' => WalletTopupRequest::where('status','approved')->sum('amount'),
        ];

        return view('livewire.admin.wallet-requests', compact('requests','summary'))
            ->layout('layouts.admin');
    }
}
