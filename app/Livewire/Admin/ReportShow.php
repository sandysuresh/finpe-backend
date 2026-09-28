<?php

namespace App\Livewire\Admin;

use App\Support\SampleWalletReport;
use App\Support\UrlId;
use App\Support\SimpleXlsx;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportShow extends Component
{
    public string $code = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(string $report): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('reports')) {
            abort(403);
        }

        $code = UrlId::decodeString($report);
        $this->dateFrom = (string) request('from', '');
        $this->dateTo = (string) request('to', '');

        if ($code === null || ! SampleWalletReport::vendor($code, $this->dateFrom, $this->dateTo)) {
            abort(404);
        }

        $this->code = $code;
    }

    public function exportExcel(): StreamedResponse
    {
        $report = SampleWalletReport::vendor($this->code, $this->dateFrom, $this->dateTo);
        $summaryRows = [
            ['Vendor code', 'Vendor', 'Opening', 'Credit', 'Debit', 'Closing'],
            [$report['code'], $report['name'], $report['opening'], $report['credit'], $report['debit'], $report['closing']],
        ];
        $ledgerRows = [['Date', 'Reference', 'Description', 'Type', 'Amount', 'Balance before', 'Balance after']];
        foreach ($report['lines'] as $line) {
            $ledgerRows[] = [
                $line['date'], $line['reference'], $line['description'], ucfirst($line['type']),
                $line['amount'], $line['balance_before'], $line['balance_after'],
            ];
        }

        return SimpleXlsx::download($this->code.'-wallet-report.xlsx', [
            'Summary' => $summaryRows,
            'Ledger' => $ledgerRows,
        ]);
    }

    public function render()
    {
        $report = SampleWalletReport::vendor($this->code, $this->dateFrom, $this->dateTo);

        return view('livewire.admin.report-show', [
            'report' => $report,
        ])->layout('layouts.admin', ['title' => $report['name'].' report']);
    }
}
