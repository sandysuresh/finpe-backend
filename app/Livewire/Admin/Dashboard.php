<?php

namespace App\Livewire\Admin;

use App\Models\CommissionEntry;
use App\Models\Merchant;
use App\Models\Settlement;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\WalletTopupRequest;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $pendingKyc = Vendor::query()->where('kyc_status', 'submitted')->count();
        $pendingWallet = WalletTopupRequest::query()->where('status', 'pending')->count();
        $failed = Transaction::query()->where('status', 'failed')->count();
        $volume = (float) Transaction::query()->where('status', 'success')->sum('amount');
        $settled = (float) Settlement::query()->where('status', 'settled')->sum('net_amount');
        $commission = (float) CommissionEntry::query()
            ->where('status', CommissionEntry::STATUS_RECORDED)
            ->sum('commission_amount');

        $primary = [
            ['label' => 'Transaction Volume', 'value' => '₹ '.number_format($volume, 2), 'hint' => 'Successful payouts', 'tone' => 'blue', 'icon' => 'volume', 'href' => route('admin.txn-success')],
            ['label' => 'Total Transactions', 'value' => number_format(Transaction::query()->count()), 'hint' => 'All partner payouts', 'tone' => 'indigo', 'icon' => 'txn', 'href' => route('admin.transactions')],
            ['label' => 'Total Partners', 'value' => number_format(Vendor::query()->count()), 'hint' => 'Partner accounts', 'tone' => 'emerald', 'icon' => 'partners', 'href' => route('admin.vendors')],
            ['label' => 'Pending KYC', 'value' => number_format($pendingKyc), 'hint' => 'Waiting for review', 'tone' => 'amber', 'icon' => 'kyc', 'href' => route('admin.partners.kyc')],
        ];

        $secondary = [
            ['label' => 'Total Users', 'value' => '0', 'tone' => 'slate', 'icon' => 'users', 'href' => route('admin.app-users.list')],
            ['label' => 'Total Merchants', 'value' => number_format(Merchant::query()->count()), 'tone' => 'violet', 'icon' => 'merchants', 'href' => route('admin.merchants')],
            ['label' => 'Total Commission', 'value' => '₹ '.number_format($commission, 2), 'tone' => 'blue', 'icon' => 'commission', 'href' => route('admin.commission.summary')],
            ['label' => 'Settlement Amount', 'value' => '₹ '.number_format($settled, 2), 'tone' => 'emerald', 'icon' => 'settlement', 'href' => route('admin.partners.settlement')],
            ['label' => 'Failed Transactions', 'value' => number_format($failed), 'tone' => 'red', 'icon' => 'failed', 'href' => route('admin.txn-failed')],
            ['label' => 'System Alerts', 'value' => number_format($pendingKyc + $pendingWallet), 'tone' => 'amber', 'icon' => 'alert', 'href' => route('admin.partners.kyc')],
        ];

        $alerts = [
            ['label' => 'Partner KYC pending', 'count' => $pendingKyc, 'href' => route('admin.partners.kyc')],
            ['label' => 'Wallet requests pending', 'count' => $pendingWallet, 'href' => route('admin.wallet-requests')],
            ['label' => 'Failed transactions', 'count' => $failed, 'href' => route('admin.txn-failed')],
        ];

        return view('livewire.admin.dashboard', [
            'primary' => $primary,
            'secondary' => $secondary,
            'alerts' => $alerts,
            'recentTransactions' => Transaction::query()->with('vendor')->latest()->limit(8)->get(),
        ])->layout('layouts.admin', ['title' => 'Dashboard']);
    }
}
