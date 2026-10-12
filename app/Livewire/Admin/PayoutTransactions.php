<?php

namespace App\Livewire\Admin;

use App\Models\Transaction;
use App\Support\CommissionProviders;
use App\Support\SimpleXlsx;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayoutTransactions extends Component
{
    use WithPagination;

    public string $status = '';
    public string $search = '';
    public string $provider = '';
    public string $dateFrom = '';
    public string $dateTo = '';

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

    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingProvider(): void { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['status', 'search', 'provider', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function exportExcel(): StreamedResponse
    {
        $rows = [['Date', 'FinPe reference', 'Merchant reference', 'Vendor', 'Vendor code', 'Provider', 'Amount', 'Charge', 'Mode', 'Beneficiary', 'Account', 'IFSC', 'Provider reference', 'Status']];
        foreach ($this->filtered()->with('vendor')->latest()->get() as $txn) {
            $rows[] = [
                $txn->created_at?->format('Y-m-d H:i:s'),
                $txn->reference,
                $txn->merchant_ref,
                $txn->vendor?->business_name,
                $txn->vendor?->vendor_code,
                CommissionProviders::name($txn->payout_provider) ?: '',
                (float) $txn->amount,
                (float) $txn->payout_charge,
                $txn->service,
                $txn->beneficiary_name,
                $this->maskAccount($txn->account_number),
                $txn->ifsc_code,
                $txn->bank_reference,
                $txn->status,
            ];
        }

        return SimpleXlsx::download('admin-payout-transactions.xlsx', ['Payouts' => $rows]);
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
        $filtered = $this->filtered();
        $transactions = (clone $filtered)->with('vendor')->latest()->paginate(20);
        $providers = Transaction::query()
            ->where('type', 'payout')
            ->whereNotNull('payout_provider')
            ->where('payout_provider', '!=', '')
            ->distinct()
            ->orderBy('payout_provider')
            ->pluck('payout_provider');
        $detail = $this->detailId
            ? Transaction::query()->with('vendor')->where('type', 'payout')->find($this->detailId)
            : null;

        return view('livewire.admin.payout-transactions', [
            'transactions' => $transactions,
            'detail' => $detail,
            'providers' => $providers,
            'summary' => [
                'count' => (clone $filtered)->count(),
                'amount' => number_format((float) (clone $filtered)->sum('amount'), 2),
                'charge' => number_format((float) (clone $filtered)->sum('payout_charge'), 2),
            ],
        ])->layout('layouts.admin', ['title' => 'Payout Transactions']);
    }

    private function filtered(): Builder
    {
        return Transaction::query()
            ->where('type', 'payout')
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->provider !== '', fn (Builder $query) => $query->where('payout_provider', $this->provider))
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->search !== '', function (Builder $query) {
                $term = '%'.$this->search.'%';
                $query->where(function (Builder $inner) use ($term) {
                    $inner->where('reference', 'like', $term)
                        ->orWhere('merchant_ref', 'like', $term)
                        ->orWhere('bank_reference', 'like', $term)
                        ->orWhere('beneficiary_name', 'like', $term);
                });
            });
    }
}
