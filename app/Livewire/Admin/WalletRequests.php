<?php
namespace App\Livewire\Admin;

use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Models\WalletTopupRequest;
use App\Support\AdminAudit;
use App\Support\VendorNotify;
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
        $needed = $this->actionType === 'approve' ? 'approve' : 'reject';
        if (! \App\Support\AdminAccess::allows('wallet-requests', $needed)) {
            $this->showApproveModal = false;

            return;
        }

        $request = WalletTopupRequest::with('vendor')->findOrFail($this->actionId);

        if ($request->status !== 'pending') {
            $this->showApproveModal = false;
            return;
        }

        $applied = false;
        DB::transaction(function () use ($request, &$applied) {
            $locked = WalletTopupRequest::query()->whereKey($request->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== 'pending') {
                return;
            }

            $applied = true;
            if ($this->actionType === 'approve') {
                $wallet = Wallet::query()->where('vendor_id', $locked->vendor_id)->lockForUpdate()->first()
                    ?? Wallet::create([
                        'vendor_id'    => $locked->vendor_id,
                        'balance'      => 0,
                        'hold_balance' => 0,
                    ]);

                $alreadyCredited = WalletLedger::query()
                    ->where('vendor_id', $locked->vendor_id)
                    ->where('reference', $locked->reference)
                    ->where('source', 'topup')
                    ->where('type', 'credit')
                    ->exists();

                if (! $alreadyCredited) {
                    $balanceBefore = (float) $wallet->balance;
                    $balanceAfter  = $balanceBefore + (float) $locked->amount;

                    $wallet->increment('balance', (float) $locked->amount);

                    WalletLedger::create([
                        'vendor_id'      => $locked->vendor_id,
                        'wallet_id'      => $wallet->id,
                        'type'           => 'credit',
                        'amount'         => $locked->amount,
                        'balance_before' => $balanceBefore,
                        'balance_after'  => $balanceAfter,
                        'reference'      => $locked->reference,
                        'description'    => 'Wallet top-up approved by admin',
                        'source'         => 'topup',
                    ]);
                }

                $locked->update([
                    'status'      => 'approved',
                    'admin_note'  => $this->adminNote,
                    'approved_by' => Auth::guard('admin')->id(),
                    'actioned_at' => now(),
                ]);
            } else {
                $locked->update([
                    'status'      => 'rejected',
                    'admin_note'  => $this->adminNote,
                    'approved_by' => Auth::guard('admin')->id(),
                    'actioned_at' => now(),
                ]);
            }
        });

        if (! $applied) {
            $this->showApproveModal = false;

            return;
        }

        $request->refresh();
        $approved = $this->actionType === 'approve';
        AdminAudit::record(
            $approved ? 'Wallet request approved' : 'Wallet request rejected',
            $approved ? 'Approved' : 'Rejected',
            'Request #'.$request->id.' '.($request->vendor->business_name ?? '').' '.$request->amount,
            'pending',
            ($approved ? 'approved' : 'rejected').($this->adminNote !== '' ? ' | '.$this->adminNote : ''),
        );

        if ($request->vendor) {
            VendorNotify::walletDecision(
                $request->vendor,
                $approved,
                (string) $request->reference,
                (string) $request->amount,
                $this->adminNote,
            );
        }

        $this->showApproveModal = false;
        $this->resetPage();
    }

    public function updatingSearch(): void  { $this->resetPage(); }

    public function render()
    {
        $requests = WalletTopupRequest::with('vendor','approvedBy','vendorBankAccount')
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
