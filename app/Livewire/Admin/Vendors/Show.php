<?php

namespace App\Livewire\Admin\Vendors;

use App\Models\ApiCredential;
use App\Models\Bank;
use App\Models\CommissionEntry;
use App\Models\VendorApiAccess;
use App\Services\CommissionService;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\VendorKycReview;
use App\Support\AdminAudit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Vendor $vendor;

    public string $tab = 'kyc';

    public string $kycComment = '';

    public string $reviewMessage = '';

    public ?string $revealedApiSecret = null;

    public array $assignedBankIds = [];

    public function mount(Vendor $vendor): void
    {
        $this->vendor = $vendor;
        $this->kycComment = '';
        $this->assignedBankIds = $vendor->banks()->pluck('banks.id')->map(fn ($id) => (string) $id)->all();

        $tab = request()->query('tab');
        if (is_string($tab) && in_array($tab, ['kyc', 'profile', 'wallet', 'transactions', 'settlements', 'beneficiaries', 'developer'], true)) {
            $this->tab = $tab;
        }
    }

    public function canReviewKyc(): bool
    {
        return $this->vendor->kyc_status === 'submitted';
    }

    public function approveKyc(): void
    {
        if (! \App\Support\AdminAccess::allows('vendors', 'approve') || ! $this->canReviewKyc()) {
            return;
        }

        $this->validate([
            'kycComment' => 'nullable|string|max:2000',
        ]);

        $comment = $this->kycComment ?: 'KYC approved.';
        $before = (string) $this->vendor->kyc_status;

        $this->vendor->update([
            'kyc_status' => 'verified',
            'kyc_comment' => $comment,
            'kyc_reviewed_at' => now(),
            'kyc_reviewed_by' => Auth::guard('admin')->id(),
        ]);

        VendorKycReview::create([
            'vendor_id' => $this->vendor->id,
            'admin_id' => Auth::guard('admin')->id(),
            'action' => 'approved',
            'comment' => $comment,
        ]);

        $this->kycComment = '';
        $this->vendor = $this->vendor->fresh();
        AdminAudit::record(
            'KYC approved',
            'Approved',
            'User #'.$this->vendor->id.' '.$this->vendor->business_name,
            $before,
            'verified | '.$comment,
        );
        $this->reviewMessage = 'KYC approved. Vendor can now see KYC Approved in their panel.';
    }

    public function rejectKyc(): void
    {
        if (! \App\Support\AdminAccess::allows('vendors', 'reject') || ! $this->canReviewKyc()) {
            return;
        }

        $this->validate([
            'kycComment' => 'required|string|min:5|max:2000',
        ]);

        $before = (string) $this->vendor->kyc_status;
        $comment = $this->kycComment;

        $this->vendor->update([
            'kyc_status' => 'rejected',
            'kyc_comment' => $this->kycComment,
            'kyc_reviewed_at' => now(),
            'kyc_reviewed_by' => Auth::guard('admin')->id(),
        ]);

        VendorKycReview::create([
            'vendor_id' => $this->vendor->id,
            'admin_id' => Auth::guard('admin')->id(),
            'action' => 'rejected',
            'comment' => $this->kycComment,
        ]);

        $this->kycComment = '';
        $this->vendor = $this->vendor->fresh();
        AdminAudit::record(
            'KYC rejected',
            'Rejected',
            'User #'.$this->vendor->id.' '.$this->vendor->business_name,
            $before,
            'rejected | '.$comment,
        );
        $this->reviewMessage = 'KYC rejected. Approve/Reject will return after the vendor resubmits.';
    }

    public function setTab(string $tab): void
    {
        $allowed = [
            'kyc', 'profile', 'wallet', 'transactions',
            'settlements', 'beneficiaries', 'developer',
        ];

        if (in_array($tab, $allowed, true)) {
            $this->tab = $tab;
            $this->resetPage();
        }
    }

    public function enablePayoutApi(): void
    {
        VendorApiAccess::query()->updateOrCreate(
            ['vendor_id' => $this->vendor->id, 'api_code' => VendorApiAccess::PAYOUT],
            [
                'is_enabled' => true,
                'assigned_at' => now(),
                'assigned_by' => Auth::guard('admin')->id(),
            ],
        );
        $this->vendor = $this->vendor->fresh();
        $this->reviewMessage = 'Payout API assigned and enabled for this vendor.';
    }

    public function disablePayoutApi(): void
    {
        $access = $this->vendor->apiAccess()->where('api_code', VendorApiAccess::PAYOUT)->first();
        if (! $access) {
            return;
        }

        $access->update([
            'is_enabled' => false,
            'assigned_at' => now(),
            'assigned_by' => Auth::guard('admin')->id(),
        ]);
        $this->vendor = $this->vendor->fresh();
        $this->reviewMessage = 'Payout API disabled for this vendor.';
    }

    public function generatePayoutCredentials(): void
    {
        if (! $this->vendor->hasEnabledApi(VendorApiAccess::PAYOUT) || $this->vendor->apiCredential) {
            return;
        }

        $this->revealedApiSecret = ApiCredential::issueFor($this->vendor);
        $this->vendor = $this->vendor->fresh();
        $this->tab = 'developer';
        $this->reviewMessage = 'API credentials generated. Copy the secret now. It will not be shown again.';
    }

    public function rotatePayoutCredentials(): void
    {
        $credential = $this->vendor->apiCredential;
        if (! $this->vendor->hasEnabledApi(VendorApiAccess::PAYOUT) || ! $credential) {
            return;
        }

        $this->revealedApiSecret = $credential->rotateSecret();
        $this->vendor = $this->vendor->fresh();
        $this->tab = 'developer';
        $this->reviewMessage = 'API secret rotated. Copy the new secret now. The previous secret no longer works.';
    }

    public function dismissRevealedApiSecret(): void
    {
        $this->revealedApiSecret = null;
    }

    public function toggleApiAccess(): void
    {
        $this->vendor->update([
            'api_enabled' => ! $this->vendor->api_enabled,
        ]);
        $this->vendor = $this->vendor->fresh();
        $this->reviewMessage = $this->vendor->api_enabled
            ? 'Vendor API access enabled. Calls still require HMAC signature and IP whitelist.'
            : 'Vendor API access disabled.';
    }

    public function saveAssignedBanks(): void
    {
        $ids = collect($this->assignedBankIds)->map(fn ($id) => (int) $id)->filter()->unique()->all();
        $sync = [];
        foreach ($ids as $id) {
            $sync[$id] = ['is_enabled' => true];
        }
        $this->vendor->banks()->sync($sync);

        if ($ids !== []) {
            $this->vendor->update(['api_enabled' => true]);
        }

        $this->vendor = $this->vendor->fresh();
        $this->reviewMessage = 'Bank APIs updated for this vendor. Endpoints will show in the vendor Developer panel.';
    }

    public function render()
    {
        $vendor = $this->vendor->load([
            'legalDetails',
            'promoters',
            'directors',
            'teamItDetails',
            'businessPlans',
            'evaluation',
            'wallet',
            'apiCredential',
            'kycReviewer',
            'kycReviews.admin',
        ]);

        $ledger = $vendor->wallet
            ? $vendor->wallet->ledger()->latest()->paginate(10, ['*'], 'ledPage')
            : null;

        $payoutByReference = collect();
        if ($ledger && $ledger->isNotEmpty()) {
            $refs = $ledger->getCollection()->pluck('reference')->filter()->unique()->values();
            if ($refs->isNotEmpty()) {
                $payoutByReference = Transaction::query()
                    ->where('type', 'payout')
                    ->where('vendor_id', $vendor->id)
                    ->whereIn('reference', $refs)
                    ->get(['reference', 'payout_provider', 'status', 'amount'])
                    ->keyBy('reference');
            }
        }

        $topups = $vendor->topupRequests()->latest()->paginate(10, ['*'], 'topPage');

        $transactions = $vendor->transactions()->with('commissionEntry')->latest()->paginate(10, ['*'], 'txnPage');

        $configuredRule = app(CommissionService::class)->configuredPayoutRule((int) $vendor->id);

        $appliedCommission = CommissionEntry::query()
            ->where('vendor_id', $vendor->id)
            ->selectRaw('count(*) as entry_count, coalesce(sum(commission_amount), 0) as total_commission')
            ->first();

        $recentCommission = CommissionEntry::query()
            ->with('sourceTransaction:id,created_at')
            ->where('vendor_id', $vendor->id)
            ->latest('id')
            ->limit(5)
            ->get();

        $txnSummary = [
            'total' => $vendor->transactions()->count(),
            'success' => $vendor->transactions()->where('status', 'success')->count(),
            'failed' => $vendor->transactions()->where('status', 'failed')->count(),
            'volume' => (float) $vendor->transactions()->where('status', 'success')->sum('amount'),
        ];

        $settlements = $vendor->settlements()->latest()->paginate(10, ['*'], 'setPage');

        $beneficiaries = $vendor->beneficiaries()->latest()->paginate(10, ['*'], 'benPage');

        $webhookLogs = $vendor->webhookLogs()->latest()->limit(15)->get();
        $allBanks = Bank::query()->where('is_active', true)->orderBy('name')->get();
        $payoutAccess = $vendor->apiAccess()->where('api_code', VendorApiAccess::PAYOUT)->first();

        return view('livewire.admin.vendors.show', compact(
            'vendor',
            'ledger',
            'payoutByReference',
            'topups',
            'transactions',
            'configuredRule',
            'appliedCommission',
            'recentCommission',
            'txnSummary',
            'settlements',
            'beneficiaries',
            'webhookLogs',
            'allBanks',
            'payoutAccess',
        ))->layout('layouts.admin', ['title' => $vendor->business_name]);
    }
}
