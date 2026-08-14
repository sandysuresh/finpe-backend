<?php

namespace App\Livewire\Admin;

use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\Wallet;
use Livewire\Component;

class Dashboard extends Component
{
    public string $range = 'month';

    public function render()
    {
        $query = Transaction::query();

        if ($this->range === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($this->range === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } else {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        $transactions = $query->get();

        $totalBalance = Wallet::sum('balance');
        $totalTransactions = $transactions->count();
        $successful = $transactions->where('status', 'success')->count();
        $pending = $transactions->where('status', 'pending')->count();
        $failed = $transactions->where('status', 'failed')->count();

        $recentTransactions = Transaction::with('vendor')
            ->latest()
            ->limit(8)
            ->get();

        $topVendors = Vendor::withCount([
            'transactions as transaction_count' => function ($q) {
                $q->where('status', 'success');
            },
        ])
        ->withSum(['transactions as transaction_amount' => function ($q) {
            $q->where('status', 'success');
        }], 'amount')
        ->orderByDesc('transaction_count')
        ->limit(5)
        ->get();

        return view('livewire.admin.dashboard', compact(
            'totalBalance',
            'totalTransactions',
            'successful',
            'pending',
            'failed',
            'recentTransactions',
            'topVendors'
        ))->layout('layouts.admin');
    }
}