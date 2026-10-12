<?php

namespace App\Livewire\Admin;

use App\Models\Transaction;
use App\Models\Vendor;
use App\Support\CommissionProviders;
use App\Support\SimpleXlsx;
use Illuminate\Database\Eloquent\Builder;
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
            'Date', 'Reference', 'Partner', 'Vendor code', 'Provider', 'Beneficiary',
            'Amount', 'Charge', 'Commission', 'Service', 'Status',
        ]];
        $exportRows = Transaction::query()->count() === 0
            ? $this->filterSamples($this->sampleTransactions())
            : $this->filtered()->with(['vendor', 'commissionEntry'])->latest()->get();
        foreach ($exportRows as $txn) {
            $rows[] = [
                $txn->created_at?->format('Y-m-d H:i:s'),
                $txn->reference,
                $txn->vendor?->business_name,
                $txn->vendor?->vendor_code,
                ($txn->type ?? null) === 'payout' ? (CommissionProviders::name($txn->payout_provider) ?: '') : '',
                $txn->beneficiary_name,
                (float) $txn->amount,
                (float) $txn->payout_charge,
                $txn->commissionEntry ? (float) $txn->commissionEntry->commission_amount : '',
                $txn->service,
                $txn->status,
            ];
        }

        return SimpleXlsx::download('admin-transactions.xlsx', [
            'Transactions' => $rows,
        ]);
    }

    public function render()
    {
        $usingSample = Transaction::query()->count() === 0;

        if ($usingSample) {
            $filtered = $this->filterSamples($this->sampleTransactions());
            $transactions = $this->paginateCollection($filtered);
            $summary = $this->summaryFromCollection($filtered);
        } else {
            $filtered = $this->filtered();
            $transactions = (clone $filtered)->with(['vendor', 'commissionEntry'])->latest()->paginate(15);
            $summary = [
                'total' => (clone $filtered)->count(),
                'success' => (clone $filtered)->where('status', 'success')->count(),
                'failed' => (clone $filtered)->where('status', 'failed')->count(),
                'pending' => (clone $filtered)->where('status', 'pending')->count(),
                'volume' => number_format((float) (clone $filtered)->where('status', 'success')->sum('amount'), 2),
            ];
        }

        $providers = Transaction::query()
            ->whereNotNull('payout_provider')
            ->where('payout_provider', '!=', '')
            ->distinct()
            ->orderBy('payout_provider')
            ->pluck('payout_provider')
            ->map(fn ($code) => ['code' => (string) $code, 'name' => CommissionProviders::name((string) $code) ?: (string) $code])
            ->all();

        return view('livewire.admin.transactions', compact('transactions', 'summary', 'usingSample', 'providers'))
            ->layout('layouts.admin', ['title' => $this->heading]);
    }

    private function filtered(): Builder
    {
        return Transaction::query()
            ->when($this->search !== '', function (Builder $query) {
                $term = '%'.$this->search.'%';
                $query->where(function (Builder $inner) use ($term) {
                    $inner->where('reference', 'like', $term)
                        ->orWhere('merchant_ref', 'like', $term)
                        ->orWhere('beneficiary_name', 'like', $term)
                        ->orWhereHas('vendor', function (Builder $vendor) use ($term) {
                            $vendor->where('business_name', 'like', $term)
                                ->orWhere('vendor_code', 'like', $term);
                        });
                });
            })
            ->when($this->status === 'reversed', fn (Builder $query) => $query->whereIn('status', ['reversed', 'refund', 'refunded']))
            ->when($this->status !== '' && $this->status !== 'reversed', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->service !== '', fn (Builder $query) => $query->where('service', $this->service))
            ->when($this->provider !== '', fn (Builder $query) => $query->where('payout_provider', $this->provider))
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo));
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
