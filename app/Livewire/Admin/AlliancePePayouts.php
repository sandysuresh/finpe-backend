<?php

namespace App\Livewire\Admin;

use App\Exceptions\AlliancePeException;
use App\Models\AlliancePePayout;
use App\Models\Vendor;
use App\Models\WalletLedger;
use App\Services\AlliancePe\AlliancePeClient;
use App\Services\AlliancePe\AlliancePePayoutService;
use App\Support\AdminAccess;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlliancePePayouts extends Component
{
    use WithPagination;

    public string $search = '';

    public string $vendorId = '';

    public string $transactionId = '';

    public string $merchantOrderId = '';

    public string $payoutId = '';

    public string $status = '';

    public string $transferMode = '';

    public string $environmentFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public ?int $selectedId = null;

    public ?string $notice = null;

    public ?string $error = null;

    public ?array $balance = null;

    public function mount(): void
    {
        $this->authorizeModule();
        $id = (int) request()->query('view', 0);
        if ($id > 0) {
            $this->show($id);
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingVendorId(): void { $this->resetPage(); }
    public function updatingTransactionId(): void { $this->resetPage(); }
    public function updatingMerchantOrderId(): void { $this->resetPage(); }
    public function updatingPayoutId(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingTransferMode(): void { $this->resetPage(); }
    public function updatingEnvironmentFilter(): void { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }

    public function checkConnection(AlliancePeClient $client): void
    {
        $this->authorizeModule();
        $this->error = null;
        $this->notice = null;
        try {
            $this->balance = $client->balance();
            $this->notice = 'AlliancePe responded.';
        } catch (AlliancePeException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function refreshSelected(AlliancePePayoutService $payouts): void
    {
        $this->authorizeModule();
        $payout = $this->selected();
        if (! $payout) {
            return;
        }
        try {
            $payouts->reconcile($payout);
            $this->notice = 'Payout status refreshed.';
        } catch (AlliancePeException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function show(int $id): void
    {
        $this->authorizeModule();
        $this->selectedId = AlliancePePayout::query()->whereKey($id)->value('id');
    }

    public function export(): StreamedResponse
    {
        $this->authorizeModule();
        if (! AdminAccess::allows('alliancepe', 'export')) {
            abort(403);
        }

        $rows = $this->historyQuery()->with('vendor')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Transaction ID', 'Payout ID', 'Merchant Order ID', 'Vendor', 'Vendor ID', 'Amount', 'FinPe commission type', 'FinPe commission rate', 'FinPe commission', 'Total wallet debit', 'Beneficiary', 'Account', 'Bank', 'IFSC', 'Mode', 'Date', 'Status', 'Wallet', 'UTR', 'Failure reason', 'Environment']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->id,
                    $row->provider_payout_id,
                    $row->merchant_order_id,
                    $row->vendor?->business_name,
                    $row->vendor_id,
                    $row->amount,
                    $row->commission_type,
                    $row->commission_rate,
                    $row->commission_amount,
                    $row->total_debited,
                    $row->beneficiary_name,
                    $row->maskedAccount(),
                    $row->bank_name,
                    $row->ifsc_code,
                    strtoupper((string) $row->transfer_mode),
                    $row->created_at?->format('Y-m-d H:i:s'),
                    $row->status,
                    $row->wallet_state,
                    $row->utr_number,
                    $row->failure_reason,
                    $row->environment ? strtoupper($row->environment) : '',
                ]);
            }
            fclose($out);
        }, 'alliancepe-payouts.csv');
    }

    public function render(AlliancePeClient $client)
    {
        $this->authorizeModule();
        $environment = 'unconfigured';
        try {
            $environment = strtoupper($client->environment());
        } catch (AlliancePeException) {
            $environment = 'UNCONFIGURED';
        }

        $selected = $this->selected();

        return view('livewire.admin.alliancepe-payouts', [
            'environment' => $environment,
            'payouts' => $this->historyQuery()->with('vendor')->paginate(15),
            'selected' => $selected,
            'timeline' => $selected ? $selected->events()->orderBy('id')->get() : collect(),
            'ledger' => $selected ? $this->ledger($selected) : collect(),
            'vendors' => Vendor::query()->orderBy('business_name')->get(['id', 'business_name', 'vendor_code']),
        ])->layout('layouts.admin', ['title' => 'AlliancePe Payouts']);
    }

    private function historyQuery()
    {
        return AlliancePePayout::query()->history([
            'search' => $this->search,
            'vendor_id' => $this->vendorId,
            'transaction_id' => $this->transactionId,
            'merchant_order_id' => $this->merchantOrderId,
            'payout_id' => $this->payoutId,
            'status' => $this->status,
            'transfer_mode' => $this->transferMode,
            'environment' => $this->environmentFilter,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ])->latest('id');
    }

    private function selected(): ?AlliancePePayout
    {
        if (! $this->selectedId) {
            return null;
        }

        return AlliancePePayout::query()->with('vendor')->find($this->selectedId);
    }

    private function ledger(AlliancePePayout $payout)
    {
        return WalletLedger::query()
            ->where('vendor_id', $payout->vendor_id)
            ->where('reference', $payout->merchant_order_id)
            ->where('source', 'alliancepe')
            ->orderBy('id')
            ->get();
    }

    private function authorizeModule(): void
    {
        abort_unless(auth('admin')->user()?->hasModule('alliancepe'), 403);
    }
}
