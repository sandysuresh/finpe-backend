<?php

namespace App\Livewire\Admin;

use App\Models\Transaction;
use App\Support\AdminPayoutHistory;
use App\Support\CommissionProviders;
use App\Support\SimpleXlsx;
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

    public string $detailSource = 'transaction';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('transactions')) {
            abort(403);
        }

        $id = (int) request()->query('view', 0);
        if ($id > 0) {
            $this->show($id, (string) request()->query('source', 'transaction'));
        }
    }

    public function revealSensitive(string $field): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('transactions')) {
            abort(403);
        }
        if (! in_array($field, ['account', 'mobile'], true) || ! $this->detailId) {
            abort(404);
        }

        $detail = app(AdminPayoutHistory::class)->find($this->detailSource, (int) $this->detailId);
        if (! $detail) {
            abort(404);
        }

        $value = $field === 'account'
            ? (string) ($detail->account_number ?? '')
            : (string) ($detail->mobile ?? $detail->beneficiary_mobile ?? '');
        if ($value === '') {
            abort(404);
        }

        $this->skipRender();
        $this->dispatch(
            'payout-sensitive-revealed',
            source: $this->detailSource,
            id: (int) $detail->id,
            field: $field,
            value: $value,
        );
    }

    public function show(int $id, string $source = 'transaction'): void
    {
        $source = $source === 'alliancepe' ? 'alliancepe' : 'transaction';
        $detail = app(AdminPayoutHistory::class)->find($source, $id);
        $this->detailSource = $detail ? $source : 'transaction';
        $this->detailId = $detail?->id;
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
        $rows = [['Date', 'FinPe reference', 'Merchant reference', 'Vendor', 'Vendor code', 'Provider', 'Amount', 'FinPe commission', 'Total wallet debit', 'Mode', 'Beneficiary', 'Account', 'IFSC', 'Provider reference', 'Status', 'Failure reason']];
        foreach (app(AdminPayoutHistory::class)->all($this->filters()) as $txn) {
            $rows[] = [
                $txn->created_at?->format('Y-m-d H:i:s'),
                $txn->reference,
                $txn->merchant_ref,
                $txn->vendor?->business_name,
                $txn->vendor?->vendor_code,
                $txn->provider_name,
                $txn->amount,
                $txn->commission_amount,
                $txn->total_debited,
                $txn->service,
                $txn->beneficiary_name,
                $this->maskAccount($txn->account_number),
                $txn->ifsc_code,
                $txn->bank_reference,
                $txn->status,
                $txn->failure_reason,
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
            'success', 'completed' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            'failed', 'cancelled' => 'bg-red-100 text-red-800 ring-red-600/20',
            default => 'bg-amber-100 text-amber-900 ring-amber-600/20',
        };
    }

    public function render()
    {
        $history = app(AdminPayoutHistory::class);
        $transactions = $history->paginate($this->filters());
        $providers = collect(array_keys(CommissionProviders::options()))
            ->merge(Transaction::query()
                ->where('type', 'payout')
                ->whereNotNull('payout_provider')
                ->where('payout_provider', '!=', '')
                ->distinct()
                ->orderBy('payout_provider')
                ->pluck('payout_provider'))
            ->unique()
            ->values();
        $detail = $this->detailId ? $history->find($this->detailSource, $this->detailId) : null;

        return view('livewire.admin.payout-transactions', [
            'transactions' => $transactions,
            'detail' => $detail,
            'providers' => $providers,
            'summary' => $history->summary($this->filters()),
        ])->layout('layouts.admin', ['title' => 'Payout Transactions']);
    }

    /**
     * @return array{status: string, provider: string, search: string, date_from: string, date_to: string}
     */
    private function filters(): array
    {
        return [
            'status' => $this->status,
            'provider' => $this->provider,
            'search' => $this->search,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ];
    }
}
