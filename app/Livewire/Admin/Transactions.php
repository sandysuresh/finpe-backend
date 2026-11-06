<?php

namespace App\Livewire\Admin;

use App\Models\AlliancePePayout;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Support\CommissionProviders;
use App\Support\SimpleXlsx;
use App\Support\VisibleTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Transactions extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $service = '';
    public string $provider = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $heading = 'All Transactions';
    public string $viewKey = 'all';

    public function mount(): void
    {
        [$this->viewKey, $this->heading, $this->status] = match (request()->route()?->getName()) {
            'admin.txn-history' => ['history', 'Transaction History', ''],
            'admin.txn-success' => ['success', 'Successful Transactions', 'success'],
            'admin.txn-pending' => ['pending', 'Pending Transactions', 'pending'],
            'admin.txn-failed' => ['failed', 'Failed Transactions', 'failed'],
            'admin.txn-reversed' => ['reversed', 'Reversed / Refund Transactions', 'reversed'],
            'admin.txn-search' => ['search', 'Transaction Search & Filter', ''],
            'admin.txn-details' => ['details', 'Transaction Details', ''],
            default => ['all', 'All Transactions', ''],
        };
    }

    public function resetFilters(): void
    {
        $locked = in_array($this->viewKey, ['success', 'pending', 'failed', 'reversed'], true) ? $this->status : '';
        $this->reset(['search', 'status', 'service', 'provider', 'dateFrom', 'dateTo']);
        $this->status = $locked;
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

    public function updatingService(): void
    {
        $this->resetPage();
    }

    public function updatingProvider(): void
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

    public function exportExcel(): StreamedResponse
    {
        $rows = [[
            'Date', 'Reference', 'Partner', 'Vendor code', 'Provider', 'Provider reference', 'UTR', 'Beneficiary',
            'Amount', 'FinPe commission', 'Total wallet debit', 'Service', 'Status', 'Failure reason',
        ]];
        $exportRows = $this->usingSample()
            ? $this->filterSamples($this->sampleTransactions())
            : app(VisibleTransactions::class)->collect($this->filters());
        foreach ($exportRows as $txn) {
            $rows[] = [
                $txn->created_at?->format('Y-m-d H:i:s'),
                $txn->reference,
                $txn->vendor?->business_name,
                $txn->vendor?->vendor_code,
                ($txn->type ?? null) === 'payout' ? (CommissionProviders::name($txn->payout_provider) ?: '') : '',
                $txn->bank_reference ?? '',
                $txn->rrn ?? '',
                $txn->beneficiary_name,
                (float) $txn->amount,
                (float) ($txn->payout_charge ?? 0),
                (float) ($txn->total_debited ?? $txn->amount),
                $txn->service,
                $txn->status,
                $txn->failure_reason ?? '',
            ];
        }

        return SimpleXlsx::download('admin-transactions.xlsx', [
            'Transactions' => $rows,
        ]);
    }

    public function render()
    {
        $usingSample = $this->usingSample();

        if ($usingSample) {
            $filtered = $this->filterSamples($this->sampleTransactions());
            $transactions = $this->paginateCollection($filtered);
            $summary = $this->summaryFromCollection($filtered);
        } else {
            $feed = app(VisibleTransactions::class);
            $transactions = $feed->paginate($this->filters(), 15, $this->getPage());
            $counts = $feed->summary($this->filters(), false);
            $summary = [
                'total' => $counts['total'],
                'success' => $counts['success'],
                'failed' => $counts['failed'],
                'pending' => $counts['pending'],
                'volume' => number_format($counts['volume'], 2),
            ];
        }

        $providers = app(VisibleTransactions::class)->providers();

        return view('livewire.admin.transactions', compact('transactions', 'summary', 'usingSample', 'providers'))
            ->layout('layouts.admin', ['title' => $this->heading]);
    }

    private function usingSample(): bool
    {
        return Transaction::query()->count() === 0 && AlliancePePayout::query()->count() === 0;
    }

    /**
     * @return array{search: string, status: string, service: string, provider: string, date_from: string, date_to: string}
     */
    private function filters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
            'service' => $this->service,
            'provider' => $this->provider,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ];
    }

    private function summaryFromCollection(Collection $rows): array
    {
        return [
            'total' => $rows->count(),
            'success' => $rows->where('status', 'success')->count(),
            'failed' => $rows->where('status', 'failed')->count(),
            'pending' => $rows->where('status', 'pending')->count(),
            'volume' => number_format((float) $rows->where('status', 'success')->sum('amount'), 2),
        ];
    }

    private function paginateCollection(Collection $rows): LengthAwarePaginator
    {
        $page = max(1, (int) $this->getPage());
        $perPage = 15;

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'page']
        );
    }

    private function filterSamples(Collection $rows): Collection
    {
        return $rows
            ->when($this->search, function (Collection $c) {
                $term = strtolower($this->search);

                return $c->filter(function ($tx) use ($term) {
                    return str_contains(strtolower((string) $tx->reference), $term)
                        || str_contains(strtolower((string) $tx->beneficiary_name), $term)
                        || str_contains(strtolower((string) ($tx->vendor?->business_name ?? $tx->vendor_name)), $term)
                        || str_contains(strtolower((string) ($tx->vendor?->vendor_code ?? $tx->vendor_code)), $term);
                });
            })
            ->when($this->status, fn (Collection $c) => $c->where('status', $this->status)->values())
            ->when($this->service, fn (Collection $c) => $c->where('service', $this->service)->values())
            ->when($this->provider, fn (Collection $c) => $c->where('payout_provider', $this->provider)->values())
            ->when($this->dateFrom, fn (Collection $c) => $c->filter(fn ($tx) => $tx->created_at->toDateString() >= $this->dateFrom)->values())
            ->when($this->dateTo, fn (Collection $c) => $c->filter(fn ($tx) => $tx->created_at->toDateString() <= $this->dateTo)->values())
            ->values();
    }

    private function sampleTransactions(): Collection
    {
        $vendors = Vendor::query()->orderBy('id')->get();

        $rows = [
            ['TXN-8F2A91', 12500.00, 'imps', 'success', 'Ram Bahadur Shrestha', 0, 3],
            ['TXN-44C1DE', 85000.50, 'neft', 'success', 'Sita Enterprises', 1, 8],
            ['TXN-91B007', 3200.00, 'imps', 'pending', 'Hari Prasad', 1, 1],
            ['TXN-C0FFEE', 150000.00, 'rtgs', 'success', 'Kathmandu Traders Pvt Ltd', 2, 14],
            ['TXN-11A2B3', 540.00, 'imps', 'failed', 'Maya Gurung', 0, 2],
            ['TXN-77E901', 24999.00, 'neft', 'success', 'Pokhara Supplies', 3, 6],
            ['TXN-AB12CD', 7800.00, 'imps', 'pending', 'Nabin Thapa', 2, 0],
            ['TXN-5D4E3F', 41000.00, 'rtgs', 'failed', 'Everest Logistics', 1, 5],
            ['TXN-90FA12', 999.00, 'imps', 'success', 'Laxmi Store', 4, 9],
            ['TXN-3344AA', 67500.00, 'neft', 'success', 'Himalayan Exports', 0, 11],
        ];

        return collect($rows)->map(function (array $row, int $i) use ($vendors) {
            $vendor = $vendors->isNotEmpty()
                ? $vendors[$row[5] % $vendors->count()]
                : null;

            return (object) [
                'id' => $i + 1,
                'reference' => $row[0],
                'amount' => $row[1],
                'service' => $row[2],
                'status' => $row[3],
                'type' => null,
                'source' => 'transaction',
                'payout_provider' => null,
                'payout_charge' => 0,
                'total_debited' => $row[1],
                'failure_reason' => null,
                'beneficiary_name' => $row[4],
                'vendor' => $vendor,
                'vendor_name' => $vendor?->business_name ?? 'Sample Vendor',
                'vendor_code' => $vendor?->vendor_code ?? 'VNDDEMO',
                'commissionEntry' => null,
                'created_at' => now()->subDays($row[6])->subHours($i),
            ];
        });
    }
}
