<?php
namespace App\Livewire\Vendor;

use App\Models\Transaction;
use App\Services\Payout\PayoutProviderRegistry;
use App\Support\RecordedCommission;
use App\Support\SimpleXlsx;
use Illuminate\Database\Eloquent\Builder;
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
        $rows = $this->query($vendor->id)->with($this->commissionWith($vendor->id))->latest()->get();
        $providers = app(PayoutProviderRegistry::class);
        $sheet = [[
            'Date', 'FinPe reference', 'Merchant reference', 'Type', 'Service', 'Provider',
            'Status', 'Amount', 'Charge', 'Commission', 'Beneficiary',
        ]];
        foreach ($rows as $txn) {
            $commission = RecordedCommission::amount($txn);
            $sheet[] = [
                $txn->created_at?->format('Y-m-d H:i:s'),
                $txn->reference,
                $txn->merchant_ref,
                $txn->type,
                $txn->service,
                $providers->displayName($txn->payout_provider),
                $txn->status,
                (float) $txn->amount,
                (float) $txn->payout_charge,
                $commission === null ? '' : $commission,
                $txn->beneficiary_name,
            ];
        }

        return SimpleXlsx::download($vendor->vendor_code.'-transactions.xlsx', [
            'Transactions' => $sheet,
        ]);
    }

    public function render()
    {
        $vendor = Auth::guard('vendor')->user();
        $filtered = $this->query($vendor->id);
        $transactions = (clone $filtered)
            ->with($this->commissionWith($vendor->id))
            ->latest()
            ->paginate(15);

        $summary = [
            'total' => (clone $filtered)->count(),
            'success' => (clone $filtered)->where('status', 'success')->count(),
            'failed' => (clone $filtered)->where('status', 'failed')->count(),
            'pending' => (clone $filtered)->where('status', 'pending')->count(),
            'volume' => number_format((float) (clone $filtered)->where('status', 'success')->sum('amount'), 2),
            'commission' => RecordedCommission::total($filtered, $vendor->id),
        ];

        return view('livewire.vendor.transaction-report', [
            'transactions' => $transactions,
            'summary' => $summary,
            'providers' => $this->providerOptions($vendor->id),
        ])->layout('layouts.vendor', ['title' => 'Transaction Report']);
    }

    private function query(int $vendorId): Builder
    {
        return Transaction::query()
            ->where('vendor_id', $vendorId)
            ->when($this->search !== '', function (Builder $query) {
                $term = '%'.$this->search.'%';
                $query->where(function (Builder $inner) use ($term) {
                    $inner->where('reference', 'like', $term)
                        ->orWhere('merchant_ref', 'like', $term)
                        ->orWhere('bank_reference', 'like', $term)
                        ->orWhere('beneficiary_name', 'like', $term);
                });
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->service !== '', fn (Builder $query) => $query->where('service', $this->service))
            ->when($this->provider !== '', fn (Builder $query) => $query->where('payout_provider', $this->provider))
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo));
    }

    /**
     * @return array<string, callable>
     */
    private function commissionWith(int $vendorId): array
    {
        return [
            'commissionEntry' => fn ($query) => $query->where('vendor_id', $vendorId),
        ];
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function providerOptions(int $vendorId): array
    {
        $providers = app(PayoutProviderRegistry::class);

        return Transaction::query()
            ->where('vendor_id', $vendorId)
            ->whereNotNull('payout_provider')
            ->where('payout_provider', '!=', '')
            ->distinct()
            ->orderBy('payout_provider')
            ->pluck('payout_provider')
            ->map(fn ($code) => ['code' => (string) $code, 'name' => $providers->displayName((string) $code)])
            ->all();
    }
}
