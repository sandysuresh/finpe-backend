<?php

namespace App\Livewire\Admin;

use App\Models\Transaction;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Livewire\Component;

class Transactions extends Component
{
    public string $search = '';
    public string $status = '';
    public string $service = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'service', 'dateFrom', 'dateTo']);
    }

    public function render()
    {
        $dbCount = Transaction::query()->count();
        $usingSample = $dbCount === 0;

        $transactions = $usingSample
            ? $this->sampleTransactions()
            : Transaction::query()
                ->with('vendor')
                ->when($this->search, function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('reference', 'like', "%{$this->search}%")
                            ->orWhere('beneficiary_name', 'like', "%{$this->search}%")
                            ->orWhereHas('vendor', function ($vq) {
                                $vq->where('business_name', 'like', "%{$this->search}%")
                                    ->orWhere('vendor_code', 'like', "%{$this->search}%");
                            });
                    });
                })
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->when($this->service, fn ($q) => $q->where('service', $this->service))
                ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->latest()
                ->limit(50)
                ->get();

        $source = $usingSample ? $this->unfilteredSamples() : Transaction::query();

        if ($usingSample) {
            $transactions = $this->filterSamples($transactions);
            $summary = [
                'total' => $source->count(),
                'success' => $source->where('status', 'success')->count(),
                'failed' => $source->where('status', 'failed')->count(),
                'pending' => $source->where('status', 'pending')->count(),
                'volume' => number_format((float) $source->where('status', 'success')->sum('amount'), 2),
            ];
        } else {
            $summary = [
                'total' => Transaction::query()->count(),
                'success' => Transaction::query()->where('status', 'success')->count(),
                'failed' => Transaction::query()->where('status', 'failed')->count(),
                'pending' => Transaction::query()->where('status', 'pending')->count(),
                'volume' => number_format((float) Transaction::query()->where('status', 'success')->sum('amount'), 2),
            ];
        }

        return view('livewire.admin.transactions', compact('transactions', 'summary', 'usingSample'))
            ->layout('layouts.admin', ['title' => 'Transactions']);
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
            ->when($this->dateFrom, fn (Collection $c) => $c->filter(fn ($tx) => $tx->created_at->toDateString() >= $this->dateFrom)->values())
            ->when($this->dateTo, fn (Collection $c) => $c->filter(fn ($tx) => $tx->created_at->toDateString() <= $this->dateTo)->values())
            ->values();
    }

    private function unfilteredSamples(): Collection
    {
        return $this->sampleTransactions();
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
                'created_at' => now()->subDays($row[6])->subHours($i),
            ];
        });
    }
}
