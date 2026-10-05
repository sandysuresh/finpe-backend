<?php

namespace App\Livewire\Admin;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PayoutTransactions extends Component
{
    use WithPagination;

    public string $status = '';

    public ?int $detailId = null;

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('transactions')) {
            abort(403);
        }

        $id = (int) request()->query('view', 0);
        if ($id > 0) {
            $this->show($id);
        }
    }

    public function show(int $id): void
    {
        $this->detailId = Transaction::query()->where('type', 'payout')->whereKey($id)->value('id');
    }

    public function maskAccount(?string $account): string
    {
        return self::maskAccountStatic($account);
    }

    public function maskMobile(?string $mobile): string
    {
        return self::maskMobileStatic($mobile);
    }

    public static function maskAccountStatic(?string $account): string
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

    public static function maskMobileStatic(?string $mobile): string
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

    public function statusBadgeClass(?string $status): string
    {
        return match ((string) $status) {
            'success' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            'failed' => 'bg-red-100 text-red-800 ring-red-600/20',
            default => 'bg-amber-100 text-amber-900 ring-amber-600/20',
        };
    }

    public function render()
    {
        $transactions = Transaction::query()
            ->with('vendor')
            ->where('type', 'payout')
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(20);

        $detail = $this->detailId
            ? Transaction::query()->with('vendor')->where('type', 'payout')->find($this->detailId)
            : null;

        return view('livewire.admin.payout-transactions', [
            'transactions' => $transactions,
            'detail' => $detail,
        ])->layout('layouts.admin', ['title' => 'Payout Transactions']);
    }
}
