<?php

namespace App\Livewire\Admin\Vendors;

use App\Models\Vendor;
use App\Models\VendorKycReview;
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

    public function mount(Vendor $vendor): void
    {
        $this->vendor = $vendor;
        $this->kycComment = '';

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
        if (! $this->canReviewKyc()) {
            return;
        }

        $this->validate([
            'kycComment' => 'nullable|string|max:2000',
        ]);

        $comment = $this->kycComment ?: 'KYC approved.';

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
        $this->reviewMessage = 'KYC approved. Vendor can now see KYC Approved in their panel.';
    }

    public function rejectKyc(): void
    {
        if (! $this->canReviewKyc()) {
            return;
        }

        $this->validate([
            'kycComment' => 'required|string|min:5|max:2000',
        ]);

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

        $topups = $vendor->topupRequests()->latest()->paginate(10, ['*'], 'topPage');

        $transactions = $vendor->transactions()->latest()->paginate(10, ['*'], 'txnPage');

        $txnSummary = [
            'total' => $vendor->transactions()->count(),
            'success' => $vendor->transactions()->where('status', 'success')->count(),
            'failed' => $vendor->transactions()->where('status', 'failed')->count(),
            'volume' => (float) $vendor->transactions()->where('status', 'success')->sum('amount'),
        ];

        $settlements = $vendor->settlements()->latest()->paginate(10, ['*'], 'setPage');

        $beneficiaries = $vendor->beneficiaries()->latest()->paginate(10, ['*'], 'benPage');

        $webhookLogs = $vendor->webhookLogs()->latest()->limit(15)->get();

        return view('livewire.admin.vendors.show', compact(
            'vendor',
            'ledger',
            'topups',
            'transactions',
            'txnSummary',
            'settlements',
            'beneficiaries',
            'webhookLogs',
        ))->layout('layouts.admin', ['title' => $vendor->business_name]);
    }
}
