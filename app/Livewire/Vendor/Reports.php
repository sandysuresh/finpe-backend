<?php

namespace App\Livewire\Vendor;

use App\Models\Transaction;
use App\Models\WalletLedger;
use App\Support\SimpleXlsx;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Component
{
    use WithPagination;

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $ledgerType = '';

    public string $status = '';

    public string $txnType = '';

    public function updatingDateFrom(): void
    {
        $this->resetPages();
    }

    public function updatingDateTo(): void
    {
        $this->resetPages();
    }

    public function updatingLedgerType(): void
    {
        $this->resetPages();
    }

    public function updatingStatus(): void
    {
        $this->resetPages();
    }

    public function updatingTxnType(): void
    {
        $this->resetPages();
    }

    public function resetFilters(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'ledgerType', 'status', 'txnType']);
        $this->resetPages();
    }

    public function exportExcel(): StreamedResponse
    {
        $vendorId = $this->vendorId();
        $summary = $this->summary($vendorId);
        $ledger = $this->ledgerQuery($vendorId)->orderBy('created_at')->orderBy('id')->get();
        $transactions = $this->transactionQuery($vendorId)->latest()->get();
        $linked = $this->transactionsForLedger($vendorId, $ledger->pluck('reference'));

        $summaryRows = [
            ['Opening balance', 'Credit', 'Debit', 'Closing balance', 'Transactions', 'Payout amount', 'Payout charge'],
            [
                $summary['opening'],
                $summary['credit'],
                $summary['debit'],
                $summary['closing'],
                $summary['transactions'],
                $summary['payout_amount'],
                $summary['payout_charge'],
            ],
        ];

        $ledgerRows = [[
            'Date', 'Reference', 'Description', 'Type', 'Amount',
            'Balance before', 'Balance after', 'Payout amount', 'Payout charge', 'Payout status', 'Payout provider',
        ]];
        foreach ($ledger as $line) {
            $txn = $linked->get($line->reference);
            $ledgerRows[] = [
                $line->created_at?->format('Y-m-d H:i:s'),
                $line->reference,
                $line->description,
                $line->type,
                (float) $line->amount,
                (float) $line->balance_before,
                (float) $line->balance_after,
                $txn ? (float) $txn->amount : '',
                $txn ? (float) $txn->payout_charge : '',
                $txn?->status ?? '',
                $txn?->payout_provider ?? '',
            ];
        }

        $transactionRows = [[
            'Date', 'FinPe reference', 'Merchant reference', 'Type', 'Service', 'Status',
            'Amount', 'Charge', 'Beneficiary', 'Provider reference', 'RRN',
        ]];
        foreach ($transactions as $txn) {
            $transactionRows[] = [
                $txn->created_at?->format('Y-m-d H:i:s'),
                $txn->reference,
                $txn->merchant_ref,
                $txn->type,
                $txn->service,
                $txn->status,
                (float) $txn->amount,
                (float) $txn->payout_charge,
                $txn->beneficiary_name,
                $txn->bank_reference,
                $txn->rrn,
            ];
        }

        $code = Auth::guard('vendor')->user()?->vendor_code ?: 'vendor';

        return SimpleXlsx::download($code.'-report.xlsx', [
            'Summary' => $summaryRows,
            'Wallet' => $ledgerRows,
            'Transactions' => $transactionRows,
        ]);
    }

    public function render()
    {
        $vendorId = $this->vendorId();
        $ledger = $this->ledgerQuery($vendorId)->latest()->paginate(15, pageName: 'ledgerPage');
        $transactions = $this->transactionQuery($vendorId)->latest()->paginate(15, pageName: 'txnPage');

        return view('livewire.vendor.reports', [
            'summary' => $this->summary($vendorId),
            'ledger' => $ledger,
            'payouts' => $this->transactionsForLedger($vendorId, $ledger->getCollection()->pluck('reference')),
            'transactions' => $transactions,
            'txnTypes' => Transaction::query()
                ->where('vendor_id', $vendorId)
                ->whereNotNull('type')
                ->distinct()
                ->orderBy('type')
                ->pluck('type'),
        ])->layout('layouts.vendor', ['title' => 'Reports']);
    }

    private function vendorId(): int
    {
        return (int) Auth::guard('vendor')->id();
    }

    private function resetPages(): void
    {
        $this->resetPage('ledgerPage');
        $this->resetPage('txnPage');
    }

    private function ledgerQuery(int $vendorId): Builder
    {
        return WalletLedger::query()
            ->where('vendor_id', $vendorId)
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->when(in_array($this->ledgerType, ['credit', 'debit'], true), fn (Builder $query) => $query->where('type', $this->ledgerType))
            ->when($this->status !== '' || $this->txnType !== '', function (Builder $query) use ($vendorId) {
                $query->whereExists(function ($sub) use ($vendorId) {
                    $sub->selectRaw('1')
                        ->from('transactions')
                        ->whereColumn('transactions.reference', 'wallet_ledger.reference')
                        ->where('transactions.vendor_id', $vendorId)
                        ->when($this->status !== '', fn ($inner) => $inner->where('transactions.status', $this->status))
                        ->when($this->txnType !== '', fn ($inner) => $inner->where('transactions.type', $this->txnType));
                });
            });
    }

    private function transactionQuery(int $vendorId): Builder
    {
        return Transaction::query()
            ->where('vendor_id', $vendorId)
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->txnType !== '', fn (Builder $query) => $query->where('type', $this->txnType));
    }

    private function transactionsForLedger(int $vendorId, $references)
    {
        $refs = collect($references)->filter()->unique()->values();
        if ($refs->isEmpty()) {
            return collect();
        }

        return Transaction::query()
            ->where('vendor_id', $vendorId)
            ->whereIn('reference', $refs)
            ->get()
            ->keyBy('reference');
    }

    private function summary(int $vendorId): array
    {
        $ledger = $this->ledgerQuery($vendorId);
        $first = (clone $ledger)->orderBy('created_at')->orderBy('id')->first();
        $last = (clone $ledger)->orderByDesc('created_at')->orderByDesc('id')->first();
        $transactions = $this->transactionQuery($vendorId);
        $carried = null;
        if (! $first && $this->ledgerType === '' && $this->status === '' && $this->txnType === '') {
            $carried = WalletLedger::query()
                ->where('vendor_id', $vendorId)
                ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '<', $this->dateFrom))
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->first();
        }
        $carriedBalance = $carried ? (float) $carried->balance_after : 0.0;

        return [
            'opening' => $first ? (float) $first->balance_before : $carriedBalance,
            'credit' => (float) (clone $ledger)->where('type', 'credit')->sum('amount'),
            'debit' => (float) (clone $ledger)->where('type', 'debit')->sum('amount'),
            'closing' => $last ? (float) $last->balance_after : $carriedBalance,
            'transactions' => (clone $transactions)->count(),
            'payout_amount' => (float) (clone $transactions)->sum('amount'),
            'payout_charge' => (float) (clone $transactions)->sum('payout_charge'),
        ];
    }
}
