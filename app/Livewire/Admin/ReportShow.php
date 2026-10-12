<?php

namespace App\Livewire\Admin;

use App\Models\Vendor;
use App\Support\AdminAccess;
use App\Support\SimpleXlsx;
use App\Support\UrlId;
use App\Support\WalletLedgerReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportShow extends Component
{
    use WithPagination;

    public string $vendor = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $provider = '';

    public string $entry = '';

    public string $status = '';

    public string $search = '';

    public string $vendorName = '';

    public string $vendorCode = '';

    public function mount(string $report): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('reports')) {
            abort(403);
        }

        $vendorId = UrlId::decode($report);
        $vendor = $vendorId ? Vendor::query()->find($vendorId) : null;
        if (! $vendor) {
            abort(404);
        }

        $this->vendor = (string) $vendor->id;
        $this->vendorName = $vendor->business_name;
        $this->vendorCode = $vendor->vendor_code;
        $this->dateFrom = (string) request('from', '');
        $this->dateTo = (string) request('to', '');
        $this->provider = (string) request('provider', '');
        $this->entry = (string) request('entry', '');
        $this->status = (string) request('status', '');
        $this->search = (string) request('q', '');
    }

    public function applyFilters(): void
    {
        $this->resetPage('ledgerPage');
    }

    public function resetFilters(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'provider', 'entry', 'status', 'search']);
        $this->resetPage('ledgerPage');
    }

    public function exportExcel(WalletLedgerReport $reports): StreamedResponse
    {
        if (! AdminAccess::allows('reports', 'export')) {
            abort(403);
        }

        $statement = $reports->statement($this->input());
        $lines = $reports->lines($statement['filters'], $statement['summary']['opening']);
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

        return SimpleXlsx::download($this->vendorCode.'-wallet-report.xlsx', [
            'Summary' => $reports->summaryRows($statement, $this->vendorName.' ('.$this->vendorCode.')'),
            'Ledger' => $ledgerRows,
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

        return view('livewire.admin.report-show', [
            'providers' => $reports->providerOptions(),
            'entries' => $reports->entryOptions(),
            'statuses' => $reports->statuses(),
            'summary' => $statement['summary'],
            'labels' => $statement['labels'],
            'note' => $statement['note'],
            'lines' => $lines,
        ])->layout('layouts.admin', ['title' => $this->vendorName.' report']);
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
}
