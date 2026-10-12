<?php

namespace App\Livewire\Admin;

use App\Support\AdminAccess;
use App\Support\SimpleXlsx;
use App\Support\WalletLedgerReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Component
{
    use WithPagination;

    public string $vendor = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $provider = '';

    public string $entry = '';

    public string $status = '';

    public string $search = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('reports')) {
            abort(403);
        }
    }

    public function applyFilters(): void
    {
        $this->resetPage('ledgerPage');
    }

    public function resetFilters(): void
    {
        $this->reset(['vendor', 'dateFrom', 'dateTo', 'provider', 'entry', 'status', 'search']);
        $this->resetPage('ledgerPage');
    }

    public function exportExcel(WalletLedgerReport $reports): StreamedResponse
    {
        if (! AdminAccess::allows('reports', 'export')) {
            abort(403);
        }

        $filters = $this->input();
        $statement = $reports->statement($filters);
        $lines = $reports->lines($statement['filters'], $statement['summary']['opening']);

        return SimpleXlsx::download('wallet-report.xlsx', [
            'Summary' => $reports->summaryRows($statement),
            'Ledger' => $this->ledgerRows($statement, $lines),
        ]);
    }

    public function render(WalletLedgerReport $reports)
    {
        $statement = $reports->statement($this->input());
        [, $lines] = $reports->page(
            $statement['filters'],
            $statement['summary']['opening'],
            max(1, (int) $this->getPage('ledgerPage')),
            25,
        );

        return view('livewire.admin.reports', [
            'vendors' => $reports->vendors(),
            'providers' => $reports->providerOptions(),
            'entries' => $reports->entryOptions(),
            'statuses' => $reports->statuses(),
            'summary' => $statement['summary'],
            'labels' => $statement['labels'],
            'note' => $statement['note'],
            'scope' => $statement['scope'],
            'lines' => $lines,
        ])->layout('layouts.admin', ['title' => 'Wallet reports']);
    }

    /**
     * @return array<string, mixed>
     */
    private function input(): array
    {
        return [
            'vendor_id' => $this->vendor,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'provider' => $this->provider,
            'entry' => $this->entry,
            'status' => $this->status,
            'search' => $this->search,
        ];
    }

    /**
     * @param  array<string, mixed>  $statement
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $lines
     * @return list<list<mixed>>
     */
    private function ledgerRows(array $statement, $lines): array
    {
        $summary = $statement['summary'];
        $ledgerRows = [[
            'Date/time', 'Vendor', 'Reference', 'Type', 'Payout provider',
            'Description', 'Credit', 'Debit', 'Running balance', 'Transaction status', 'Transaction reference',
        ]];
        foreach ($lines as $line) {
            $ledgerRows[] = [
                optional($line['at'])->format('Y-m-d H:i:s'),
                $line['vendor_name'].' ('.$line['vendor_code'].')',
                $line['reference'],
                $line['entry'],
                $line['provider'],
                $line['description'],
                $line['credit'],
                $line['debit'],
                $line['running'],
                $line['txn_status'],
                $line['txn_reference'],
            ];
        }
        $ledgerRows[] = [];
        $ledgerRows[] = ['', '', '', '', '', 'Totals', $summary['credit'], $summary['debit'], $summary['closing'], '', ''];

        return $ledgerRows;
    }
}
