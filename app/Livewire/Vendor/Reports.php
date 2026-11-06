<?php

namespace App\Livewire\Vendor;

use App\Models\AlliancePePayout;
use App\Models\Transaction;
use App\Models\WalletLedger;
use App\Support\AssignedPayoutProviders;
use App\Support\CommissionProviders;
use App\Support\SimpleXlsx;
use App\Support\VisibleTransactions;
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
        $transactions = app(VisibleTransactions::class)->collect($this->filters($vendorId));
        $linked = app(VisibleTransactions::class)->payoutsByReference($vendorId, $ledger->pluck('reference'));

        $summaryRows = [
            ['Opening balance', 'Credit', 'Debit', 'Closing balance', 'Transactions', 'Transaction amount', 'FinPe commission'],
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
            'Balance before', 'Balance after', 'Payout amount', 'FinPe commission', 'Payout status', 'Payout provider',
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
            'Amount', 'FinPe commission', 'Total wallet debit', 'Beneficiary', 'Provider', 'Provider reference', 'RRN', 'Failure reason',
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
                (float) ($txn->payout_charge ?? 0),
                (float) ($txn->total_debited ?? $txn->amount),
                $txn->beneficiary_name,
                CommissionProviders::name($txn->payout_provider),
                $txn->bank_reference,
                $txn->rrn,
                $txn->failure_reason,
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
        $feed = app(VisibleTransactions::class);
        $ledger = $this->ledgerQuery($vendorId)->latest()->paginate(15, pageName: 'ledgerPage');
        $transactions = $feed->paginate($this->filters($vendorId), 15, $this->getPage('txnPage'), 'txnPage');
        $types = Transaction::query()
            ->where('vendor_id', $vendorId)
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
        if (AssignedPayoutProviders::allows($vendorId, CommissionProviders::ALLIANCEPE)
            && AlliancePePayout::query()->where('vendor_id', $vendorId)->exists()
            && ! $types->contains('payout')) {
            $types->push('payout');
        }

        return view('livewire.vendor.reports', [
            'summary' => $this->summary($vendorId),
            'ledger' => $ledger,
            'payouts' => $feed->payoutsByReference($vendorId, $ledger->getCollection()->pluck('reference'), AssignedPayoutProviders::codes($vendorId)),
            'transactions' => $transactions,
            'providers' => $feed->providers($vendorId, AssignedPayoutProviders::codes($vendorId)),
            'txnTypes' => $types,
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
        $query = WalletLedger::query()
            ->where('vendor_id', $vendorId);
        AssignedPayoutProviders::restrictLedger($query, $vendorId);

        return $query
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->when(in_array($this->ledgerType, ['credit', 'debit'], true), fn (Builder $query) => $query->where('type', $this->ledgerType))
            ->when($this->status !== '' || $this->txnType !== '', function (Builder $query) {
                app(VisibleTransactions::class)->constrainLedger($query, $this->status, $this->txnType);
            });
    }

    /**
     * @return array{vendor_id: int, search: string, status: string, provider: string, date_from: string, date_to: string, type: string}
     */
    private function filters(int $vendorId): array
    {
        $allowed = AssignedPayoutProviders::codes($vendorId);
        $provider = strtolower(trim($this->provider));
        if ($provider !== '' && ! in_array($provider, $allowed, true)) {
            $provider = '__unassigned__';
        }

        return [
            'vendor_id' => $vendorId,
            'search' => $this->search,
            'status' => $this->status,
            'provider' => $provider,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'type' => $this->txnType,
            'allowed_providers' => $allowed,
        ];
    }

    private function summary(int $vendorId): array
    {
        $ledger = $this->ledgerQuery($vendorId);
        $counts = app(VisibleTransactions::class)->summary($this->filters($vendorId));
        $credit = (float) (clone $ledger)->where('type', 'credit')->sum('amount');
        $debit = (float) (clone $ledger)->where('type', 'debit')->sum('amount');
        $opening = $this->openingBalance($vendorId);

        return [
            'opening' => $opening,
            'credit' => $credit,
            'debit' => $debit,
            'closing' => round($opening + $credit - $debit, 2),
            'transactions' => $counts['total'],
            'payout_amount' => $counts['amount'],
            'payout_charge' => $counts['charge'],
        ];
    }

    private function openingBalance(int $vendorId): float
    {
        $walletScoped = $this->ledgerType === '' && $this->status === '' && $this->txnType === '';
        $prior = WalletLedger::query()
            ->where('vendor_id', $vendorId);
        AssignedPayoutProviders::restrictLedger($prior, $vendorId);
        $prior = $prior
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '<', $this->dateFrom))
            ->when(in_array($this->ledgerType, ['credit', 'debit'], true), fn (Builder $query) => $query->where('type', $this->ledgerType))
            ->when($this->status !== '' || $this->txnType !== '', function (Builder $query) {
                app(VisibleTransactions::class)->constrainLedger($query, $this->status, $this->txnType);
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
