<?php

namespace App\Livewire\Vendor;

use App\Models\Transaction;
use App\Models\VendorApiAccess;
use App\Models\WalletLedger;
use App\Services\Payout\PayoutProviderRegistry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PayoutApi extends Component
{
    use WithPagination;

    public string $section = 'transactions';

    public string $status = '';

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $ledgerType = '';

    public ?int $detailId = null;

    public string $providerCode = '';

    public function mount(PayoutProviderRegistry $providers): void
    {
        $catalog = $providers->catalog();
        $assigned = VendorApiAccess::assignedProviderCode((int) Auth::guard('vendor')->id());
        $active = collect($catalog)->firstWhere('active', true);
        $assignedKnown = $assigned && collect($catalog)->contains(fn (array $row): bool => $row['code'] === $assigned);
        $this->providerCode = $assignedKnown ? $assigned : ($active['code'] ?? '');

        $id = (int) request()->query('view', 0);
        if ($id > 0) {
            $this->section = 'transactions';
            $this->show($id);
        }
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
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

    public function updatingLedgerType(): void
    {
        $this->resetPage();
    }

    public function selectProvider(string $code): void
    {
        $code = strtolower(trim($code));
        $known = collect(app(PayoutProviderRegistry::class)->catalog())
            ->contains(fn (array $row): bool => $row['code'] === $code);
        if (! $known || $code === $this->providerCode) {
            return;
        }

        $this->providerCode = $code;
        $this->detailId = null;
        $this->resetPage();
    }

    public function setSection(string $section): void
    {
        if (! in_array($section, ['transactions', 'wallet', 'docs'], true)) {
            return;
        }

        $this->section = $section;
        $this->detailId = null;
        $this->resetPage();
    }

    public function show(int $id): void
    {
        $vendorId = Auth::guard('vendor')->id();
        $txn = Transaction::query()
            ->where('vendor_id', $vendorId)
            ->where('type', 'payout')
            ->whereKey($id)
            ->first(['id', 'payout_provider']);
        $this->detailId = $txn?->id;
        $code = strtolower(trim((string) $txn?->payout_provider));
        if ($code !== '' && collect(app(PayoutProviderRegistry::class)->catalog())->contains(fn (array $row): bool => $row['code'] === $code)) {
            $this->providerCode = $code;
        }
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
    }

    public function maskAccount(?string $account): string
    {
        $account = (string) $account;
        if ($account === '') {
            return '—';
        }
        if (strlen($account) <= 4) {
            return str_repeat('X', strlen($account));
        }

        return str_repeat('X', strlen($account) - 4).substr($account, -4);
    }

    public function maskMobile(?string $mobile): string
    {
        $mobile = preg_replace('/\D+/', '', (string) $mobile) ?? '';
        if ($mobile === '') {
            return '—';
        }
        if (strlen($mobile) <= 4) {
            return str_repeat('X', strlen($mobile));
        }

        return str_repeat('X', strlen($mobile) - 4).substr($mobile, -4);
    }

    public function statusBadgeClass(?string $status): string
    {
        return match ((string) $status) {
            'success' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            'failed' => 'bg-red-100 text-red-800 ring-red-600/20',
            default => 'bg-amber-100 text-amber-900 ring-amber-600/20',
        };
    }

    public function providerName(?string $code): string
    {
        return app(PayoutProviderRegistry::class)->displayName($code);
    }

    public function render()
    {
        $vendor = Auth::guard('vendor')->user();
        $enabled = $vendor->hasEnabledApi(VendorApiAccess::PAYOUT);
        $transactions = null;
        $detail = null;
        $ledger = null;
        $payoutByReference = collect();

        if ($enabled && $this->section === 'transactions') {
            $transactions = Transaction::query()
                ->where('vendor_id', $vendor->id)
                ->where('type', 'payout')
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->when($this->search !== '', function ($query) {
                    $term = '%'.$this->search.'%';
                    $query->where(function ($inner) use ($term) {
                        $inner->where('reference', 'like', $term)
                            ->orWhere('merchant_ref', 'like', $term)
                            ->orWhere('bank_reference', 'like', $term);
                    });
                })
                ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
                ->when($this->providerCode !== '', fn ($query) => $query->where('payout_provider', $this->providerCode))
                ->latest()
                ->paginate(15);

            $detail = $this->detailId
                ? Transaction::query()
                    ->where('vendor_id', $vendor->id)
                    ->where('type', 'payout')
                    ->whereKey($this->detailId)
                    ->first()
                : null;
        }

        if ($enabled && $this->section === 'wallet') {
            $ledger = WalletLedger::query()
                ->where('vendor_id', $vendor->id)
                ->when($this->ledgerType !== '', fn ($query) => $query->where('type', $this->ledgerType))
                ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
                ->when($this->search !== '', fn ($query) => $query->where('reference', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(15);

            $refs = $ledger->getCollection()->pluck('reference')->filter()->unique()->values();
            if ($refs->isNotEmpty()) {
                $payoutByReference = Transaction::query()
                    ->where('vendor_id', $vendor->id)
                    ->where('type', 'payout')
                    ->whereIn('reference', $refs)
                    ->get(['id', 'reference', 'amount', 'payout_charge', 'payout_provider', 'status'])
                    ->keyBy('reference');
            }
        }

        $registry = app(PayoutProviderRegistry::class);
        $catalog = $registry->catalog();
        $assignedCode = VendorApiAccess::assignedProviderCode((int) $vendor->id);
        $assigned = collect($catalog)->firstWhere('code', $assignedCode) ?? collect($catalog)->firstWhere('active', true);
        $payoutProviderName = $assigned['name'] ?? $registry->displayName($assignedCode);
        $payoutEnvironment = (string) ($assigned['environment'] ?? '');

        return view('livewire.vendor.payout-api', [
            'enabled' => $enabled,
            'transactions' => $transactions,
            'detail' => $detail,
            'ledger' => $ledger,
            'payoutByReference' => $payoutByReference,
            'providers' => $catalog,
            'payoutProviderName' => $payoutProviderName,
            'payoutEnvironment' => $payoutEnvironment,
            'apiBase' => rtrim((string) config('app.url'), '/').'/api',
        ])->layout('layouts.vendor', ['title' => 'Payout API']);
    }
}
