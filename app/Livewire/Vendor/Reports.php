<?php

namespace App\Livewire\Vendor;

use App\Models\Transaction;
use App\Models\WalletLedger;
use App\Services\Payout\PayoutProviderRegistry;
use App\Support\RecordedCommission;
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

    public string $provider = '';

    public string $search = '';

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

    public function updatingProvider(): void
    {
        $this->resetPages();
    }

    public function updatingSearch(): void
    {
        $this->resetPages();
    }

    public function resetFilters(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'ledgerType', 'status', 'txnType', 'provider', 'search']);
        $this->resetPages();
    }

    public function exportExcel(): StreamedResponse
    {
        $vendorId = $this->vendorId();
        $summary = $this->summary($vendorId);
        $ledger = $this->ledgerQuery($vendorId)->orderBy('created_at')->orderBy('id')->get();
        $transactions = $this->transactionQuery($vendorId)->with($this->commissionWith($vendorId))->latest()->get();
        $linked = $this->transactionsForLedger($vendorId, $ledger->pluck('reference'));

        $summaryRows = [
            ['Opening balance', 'Credit', 'Debit', 'Closing balance', 'Transactions', 'Transaction amount', 'Charge', 'Recorded commission'],
            [
                $summary['opening'],
                $summary['credit'],
                $summary['debit'],
                $summary['closing'],
                $summary['transactions'],
                $summary['payout_amount'],
                $summary['payout_charge'],
                $summary['commission'],
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
            'Amount', 'Charge', 'Commission', 'Beneficiary', 'Provider', 'Provider reference', 'RRN',
        ]];
        $providers = app(PayoutProviderRegistry::class);
        foreach ($transactions as $txn) {
            $commission = RecordedCommission::amount($txn);
            $transactionRows[] = [
                $txn->created_at?->format('Y-m-d H:i:s'),
                $txn->reference,
                $txn->merchant_ref,
                $txn->type,
                $txn->service,
                $txn->status,
                (float) $txn->amount,
                (float) $txn->payout_charge,
                $commission === null ? '' : $commission,
                $txn->beneficiary_name,
                $providers->displayName($txn->payout_provider),
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
        $transactions = $this->transactionQuery($vendorId)->with($this->commissionWith($vendorId))->latest()->paginate(15, pageName: 'txnPage');

        return view('livewire.vendor.reports', [
            'summary' => $this->summary($vendorId),
            'ledger' => $ledger,
            'payouts' => $this->transactionsForLedger($vendorId, $ledger->getCollection()->pluck('reference')),
            'transactions' => $transactions,
            'providers' => $this->providerOptions($vendorId),
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
            ->when($this->txnType !== '', fn (Builder $query) => $query->where('type', $this->txnType))
            ->when($this->provider !== '', fn (Builder $query) => $query->where('payout_provider', $this->provider))
            ->when($this->search !== '', function (Builder $query) {
                $term = '%'.$this->search.'%';
                $query->where(function (Builder $inner) use ($term) {
                    $inner->where('reference', 'like', $term)
                        ->orWhere('merchant_ref', 'like', $term)
                        ->orWhere('bank_reference', 'like', $term);
                });
            });
    }

    /**
     * @return array<string, callable>
     */
    private function commissionWith(int $vendorId): array
    {
        return [
            'commissionEntry' => fn ($query) => $query->where('vendor_id', $vendorId),
        ];
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function providerOptions(int $vendorId): array
    {
        $providers = app(PayoutProviderRegistry::class);

        return Transaction::query()
            ->where('vendor_id', $vendorId)
            ->whereNotNull('payout_provider')
            ->where('payout_provider', '!=', '')
            ->distinct()
            ->orderBy('payout_provider')
            ->pluck('payout_provider')
            ->map(fn ($code) => ['code' => (string) $code, 'name' => $providers->displayName((string) $code)])
            ->all();
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
        $transactions = $this->transactionQuery($vendorId);
        $credit = (float) (clone $ledger)->where('type', 'credit')->sum('amount');
        $debit = (float) (clone $ledger)->where('type', 'debit')->sum('amount');
        $opening = $this->openingBalance($vendorId);

        return [
            'opening' => $opening,
            'credit' => $credit,
            'debit' => $debit,
            'closing' => round($opening + $credit - $debit, 2),
            'transactions' => (clone $transactions)->count(),
            'payout_amount' => (float) (clone $transactions)->sum('amount'),
            'payout_charge' => (float) (clone $transactions)->sum('payout_charge'),
            'commission' => RecordedCommission::total($transactions, $vendorId),
        ];
    }

    private function openingBalance(int $vendorId): float
    {
        $walletScoped = $this->ledgerType === '' && $this->status === '' && $this->txnType === '';
        $prior = WalletLedger::query()
            ->where('vendor_id', $vendorId)
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '<', $this->dateFrom))
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

        if ($walletScoped && $this->dateFrom === '') {
            return (float) (WalletLedger::query()
                ->where('vendor_id', $vendorId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->value('balance_before') ?? 0);
        }

        if ($walletScoped) {
            return (float) ((clone $prior)->orderByDesc('created_at')->orderByDesc('id')->value('balance_after') ?? 0);
        }

        if ($this->dateFrom === '') {
            return 0.0;
        }

        $credits = (float) (clone $prior)->where('type', 'credit')->sum('amount');
        $debits = (float) (clone $prior)->where('type', 'debit')->sum('amount');

        return round($credits - $debits, 2);
    }
}
