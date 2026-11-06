<?php
namespace App\Livewire\Vendor;

use App\Support\AssignedPayoutProviders;
use App\Support\CommissionProviders;
use App\Support\SimpleXlsx;
use App\Support\VisibleTransactions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionReport extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $service = '';

    public string $provider = '';

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'dateFrom', 'dateTo', 'service', 'provider']);
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingService(): void
    {
        $this->resetPage();
    }

    public function updatingProvider(): void
    {
        $this->resetPage();
    }

    public function exportExcel(): StreamedResponse
    {
        $vendor = Auth::guard('vendor')->user();
        $rows = app(VisibleTransactions::class)->collect($this->filters($vendor->id));
        $sheet = [[
            'Date', 'FinPe reference', 'Merchant reference', 'Type', 'Service', 'Provider', 'Provider reference', 'UTR',
            'Status', 'Amount', 'FinPe commission', 'Total wallet debit', 'Beneficiary', 'Failure reason',
        ]];
        foreach ($rows as $txn) {
            $sheet[] = [
                $txn->created_at?->format('Y-m-d H:i:s'),
                $txn->reference,
                $txn->merchant_ref,
                $txn->type,
                $txn->service,
                CommissionProviders::name($txn->payout_provider),
                $txn->bank_reference,
                $txn->rrn,
                $txn->status,
                (float) $txn->amount,
                (float) ($txn->payout_charge ?? 0),
                (float) ($txn->total_debited ?? $txn->amount),
                $txn->beneficiary_name,
                $txn->failure_reason,
            ];
        }

        return SimpleXlsx::download($vendor->vendor_code.'-transactions.xlsx', [
            'Transactions' => $sheet,
        ]);
    }

    public function render()
    {
        $vendor = Auth::guard('vendor')->user();
        $feed = app(VisibleTransactions::class);
        $filters = $this->filters($vendor->id);
        $transactions = $feed->paginate($filters, 15, $this->getPage());
        $counts = $feed->summary($filters);
        $summary = [
            'total' => $counts['total'],
            'success' => $counts['success'],
            'failed' => $counts['failed'],
            'pending' => $counts['pending'],
            'volume' => number_format($counts['volume'], 2),
            'charge' => $counts['charge'],
        ];

        return view('livewire.vendor.transaction-report', [
            'transactions' => $transactions,
            'summary' => $summary,
            'providers' => $feed->providers($vendor->id, AssignedPayoutProviders::codes($vendor)),
        ])->layout('layouts.vendor', ['title' => 'Transaction Report']);
    }

    /**
     * @return array{vendor_id: int, search: string, status: string, service: string, provider: string, date_from: string, date_to: string}
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
            'service' => $this->service,
            'provider' => $provider,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'allowed_providers' => $allowed,
        ];
    }
}
