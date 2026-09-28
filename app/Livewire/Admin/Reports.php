<?php

namespace App\Livewire\Admin;

use App\Support\SampleWalletReport;
use App\Support\UrlId;
use App\Support\SimpleXlsx;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Component
{
    public string $vendor = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('reports')) {
            abort(403);
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['vendor', 'dateFrom', 'dateTo']);
    }

    public function exportExcel(): StreamedResponse
    {
        $report = SampleWalletReport::statement($this->vendor !== '' ? $this->vendor : null, $this->dateFrom, $this->dateTo);
        $summaryRows = [['Vendor code', 'Vendor', 'Opening', 'Credit', 'Debit', 'Closing']];

        foreach ($report['rows'] as $row) {
            $summaryRows[] = [$row['code'], $row['name'], $row['opening'], $row['credit'], $row['debit'], $row['closing']];
        }

        $summaryRows[] = ['', 'Total', $report['summary']['opening'], $report['summary']['credit'], $report['summary']['debit'], $report['summary']['closing']];

        $ledgerRows = [['Date', 'Vendor code', 'Vendor', 'Reference', 'Description', 'Type', 'Amount', 'Balance before', 'Balance after']];
        foreach ($report['lines'] as $line) {
            $ledgerRows[] = [
                $line['date'], $line['vendor_code'], $line['vendor'], $line['reference'],
                $line['description'], ucfirst($line['type']), $line['amount'], $line['balance_before'], $line['balance_after'],
            ];
        }

        return SimpleXlsx::download('wallet-report.xlsx', [
            'Summary' => $summaryRows,
            'Ledger' => $ledgerRows,
        ]);
    }

    public function render()
    {
        $report = SampleWalletReport::statement($this->vendor !== '' ? $this->vendor : null, $this->dateFrom, $this->dateTo);

        $rows = $report['rows']->map(fn (array $row) => $row + [
            'token' => UrlId::encode($row['code']),
        ]);

        return view('livewire.admin.reports', [
            'vendors' => SampleWalletReport::vendors(),
            'rows' => $rows,
            'summary' => $report['summary'],
        ])->layout('layouts.admin', ['title' => 'Reports']);
    }
}
