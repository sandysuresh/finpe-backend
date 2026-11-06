<?php

namespace App\Livewire\Vendor;

use App\Models\VendorApiAccess;
use App\Models\WalletLedger;
use App\Support\AssignedPayoutProviders;
use App\Services\Payout\PayoutProviderRegistry;
use App\Support\CommissionProviders;
use App\Support\SimpleXlsx;
use App\Support\VisibleTransactions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayoutApi extends Component
{
    use WithPagination;

    public string $section = 'transactions';

    public string $status = '';

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $service = '';

    public string $ledgerType = '';

    public ?int $detailId = null;

    public string $detailSource = 'transaction';

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
            $this->show($id, (string) request()->query('source', 'transaction'));
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

    public function updatingService(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['status', 'search', 'dateFrom', 'dateTo', 'service', 'ledgerType']);
        $this->resetPage();
    }

    public function exportExcel(): StreamedResponse
    {
        $vendor = Auth::guard('vendor')->user();
        $rows = app(VisibleTransactions::class)->collect($this->filters($vendor->id));
        $sheet = [[
            'Date', 'FinPe reference', 'Merchant reference', 'Service', 'Provider', 'Status',
            'Amount', 'FinPe commission', 'Total wallet debit', 'Beneficiary', 'Account',
        ]];
        foreach ($rows as $txn) {
            $sheet[] = [
                $txn->created_at?->format('Y-m-d H:i:s'),
                $txn->reference,
                $txn->merchant_ref,
                $txn->service,
                $this->providerName($txn->payout_provider),
                $txn->status,
                (float) $txn->amount,
                (float) $txn->payout_charge,
                (float) ($txn->total_debited ?? ((float) $txn->amount + (float) $txn->payout_charge)),
                $txn->beneficiary_name,
                $this->maskAccount($txn->account_number),
            ];
        }
        $code = $vendor->vendor_code ?: 'vendor';

        return SimpleXlsx::download($code.'-payout-transactions.xlsx', [
            'Payout transactions' => $sheet,
        ]);
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
        if (! $known || $code === $this->providerCode || ! AssignedPayoutProviders::allows((int) Auth::guard('vendor')->id(), $code)) {
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

    public function show(int $id, string $source = 'transaction'): void
    {
        $vendorId = (int) Auth::guard('vendor')->id();
        $allowed = AssignedPayoutProviders::codes($vendorId);
        $source = $source === 'alliancepe' ? 'alliancepe' : 'transaction';
        if (! in_array($source === 'alliancepe' ? CommissionProviders::ALLIANCEPE : CommissionProviders::VIMOPAY, $allowed, true)) {
            $this->detailId = null;

            return;
        }
        $txn = app(VisibleTransactions::class)->findForVendor($vendorId, $id, $source, $allowed);
        $this->detailId = $txn?->id;
        $this->detailSource = $txn ? $source : 'transaction';
        $code = strtolower(trim((string) ($txn->payout_provider ?? '')));
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
            'success', 'completed' => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            'failed', 'cancelled', 'rejected' => 'bg-red-100 text-red-800 ring-red-600/20',
            default => 'bg-amber-100 text-amber-900 ring-amber-600/20',
        };
    }

    /**
     * @return array{vendor_id: int, payouts_only: bool, always_include_alliance: bool, status: string, service: string, search: string, date_from: string, date_to: string, provider: string}
     */
    private function filters(int $vendorId): array
    {
        $allowed = AssignedPayoutProviders::codes($vendorId);

        return [
            'vendor_id' => $vendorId,
            'payouts_only' => true,
            'always_include_alliance' => in_array(CommissionProviders::ALLIANCEPE, $allowed, true),
            'allowed_providers' => $allowed,
            'status' => $this->status,
            'service' => $this->service,
            'search' => $this->search,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'provider' => $this->providerCode,
        ];
    }

    public function providerName(?string $code): string
    {
        $name = CommissionProviders::name($code);

        return $name && $name !== strtolower(trim((string) $code))
            ? $name
            : app(PayoutProviderRegistry::class)->displayName($code);
    }

    public function render()
    {
        $vendor = Auth::guard('vendor')->user();
        AssignedPayoutProviders::assertAllows($vendor, CommissionProviders::VIMOPAY);
        $enabled = $vendor->hasEnabledApi(VendorApiAccess::PAYOUT);
        $transactions = null;
        $detail = null;
        $ledger = null;
        $payoutByReference = collect();

        if ($enabled && $this->section === 'transactions') {
            $transactions = app(VisibleTransactions::class)->paginate($this->filters((int) $vendor->id), 15, $this->getPage());

            $detail = $this->detailId
                ? app(VisibleTransactions::class)->findForVendor((int) $vendor->id, (int) $this->detailId, $this->detailSource, AssignedPayoutProviders::codes($vendor))
                : null;
        }

        if ($enabled && $this->section === 'wallet') {
            $ledger = WalletLedger::query()
                ->where('vendor_id', $vendor->id);
            AssignedPayoutProviders::restrictLedger($ledger, $vendor);
            $ledger = $ledger
                ->when($this->ledgerType !== '', fn ($query) => $query->where('type', $this->ledgerType))
                ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
                ->when($this->search !== '', fn ($query) => $query->where('reference', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(15);

            $refs = $ledger->getCollection()->pluck('reference')->filter()->unique()->values();
            if ($refs->isNotEmpty()) {
                $payoutByReference = app(VisibleTransactions::class)->payoutsByReference((int) $vendor->id, $refs, AssignedPayoutProviders::codes($vendor));
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
