<?php

namespace App\Livewire\Admin;

use App\Models\CommissionEntry;
use App\Models\Merchant;
use App\Models\Vendor;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommissionHistory extends Component
{
    use WithPagination;

    public string $vendorId = '';
    public string $merchantId = '';
    public string $service = '';
    public string $sourceType = '';
    public string $status = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public ?int $detailId = null;

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('commission')) {
            abort(403);
        }

        $this->vendorId = (string) request()->query('vendor', '');
        $this->merchantId = (string) request()->query('merchant', '');
    }

    public function updated(string $name): void
    {
        if (in_array($name, ['vendorId', 'merchantId', 'service', 'sourceType', 'status', 'dateFrom', 'dateTo'], true)) {
            if (! AdminAccess::allows('commission', 'search')) {
                abort(403);
            }
            $this->resetPage();
        }
    }

    public function show(int $id): void
    {
        $this->detailId = $id;
    }

    public function export(): StreamedResponse
    {
        if (! AdminAccess::allows('commission', 'export')) {
            abort(403);
        }

        $rows = $this->query()->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Vendor', 'Merchant', 'Service', 'Type', 'Reference', 'Source', 'Source status', 'Base', 'Calc', 'Rate', 'Commission', 'Status']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->created_at?->format('Y-m-d H:i'),
                    $row->vendor_name_snapshot,
                    $row->merchant_code_snapshot,
                    $row->service_snapshot,
                    $row->type_snapshot,
                    $row->source_reference,
                    $row->source_type,
                    $row->source_status_snapshot,
                    $row->base_amount,
                    $row->calc_type,
                    $row->rate_value,
                    $row->commission_amount,
                    $row->status,
                ]);
            }
            fclose($out);
        }, 'commission-history.csv');
    }

    public function render()
    {
        return view('livewire.admin.commission-history', [
            'entries' => $this->query()->paginate(20),
            'detail' => $this->detailId ? CommissionEntry::query()->find($this->detailId) : null,
            'vendors' => Vendor::query()->orderBy('business_name')->get(['id', 'business_name']),
            'merchants' => Merchant::query()->orderBy('code')->get(['id', 'code']),
            'canExport' => AdminAccess::allows('commission', 'export'),
        ])->layout('layouts.admin', ['title' => 'Commission History']);
    }

    private function query()
    {
        return CommissionEntry::query()
            ->with('sourceTransaction:id,created_at')
            ->when($this->vendorId !== '', fn ($q) => $q->where('vendor_id', $this->vendorId))
            ->when($this->merchantId !== '', fn ($q) => $q->where('merchant_id', $this->merchantId))
            ->when($this->service !== '', fn ($q) => $q->where('service_snapshot', $this->service))
            ->when($this->sourceType !== '', fn ($q) => $q->where('source_type', $this->sourceType))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('id');
    }
}
