<?php

namespace App\Livewire\Vendor;

use App\Support\SampleWalletReport;
use App\Support\SimpleXlsx;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Component
{
    public string $dateFrom = '';

    public string $dateTo = '';

    public function resetFilters(): void
    {
        $this->reset(['dateFrom', 'dateTo']);
    }

    public function exportExcel(): StreamedResponse
    {
        $report = $this->report();
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

        return SimpleXlsx::download($report['code'].'-wallet-report.xlsx', [
            'Summary' => $summaryRows,
            'Ledger' => $ledgerRows,
        ]);
    }

    public function render()
    {
        return view('livewire.vendor.reports', [
            'report' => $this->report(),
        ])->layout('layouts.vendor', ['title' => 'Reports']);
    }

    private function report(): array
    {
        $vendor = Auth::guard('vendor')->user();
        $name = $vendor->business_name ?: ($vendor->contact_name ?: $vendor->email);

        return SampleWalletReport::forAccount(
            (string) ($vendor->vendor_code ?: 'VENDOR'),
            (string) $name,
            $this->dateFrom,
            $this->dateTo,
        );
    }
}
