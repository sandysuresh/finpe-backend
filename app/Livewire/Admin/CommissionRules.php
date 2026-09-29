<?php

namespace App\Livewire\Admin;

use App\Models\CommissionRule;
use App\Models\Merchant;
use App\Models\Vendor;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CommissionRules extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterVendor = '';
    public string $filterMerchant = '';
    public string $filterService = '';
    public string $filterType = '';
    public string $filterStatus = '';

    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $vendorId = '';
    public string $merchantId = '';
    public string $service = '';
    public string $type = '';
    public string $calcType = 'percentage';
    public string $value = '0';
    public string $effectiveFrom = '';
    public string $effectiveTo = '';
    public string $priority = '';
    public string $status = 'active';

    public function mount(): void
    {
        $this->authorizeView();
    }

    public function updated(string $name): void
    {
        if (in_array($name, ['search', 'filterVendor', 'filterMerchant', 'filterService', 'filterType', 'filterStatus'], true)) {
            if (! AdminAccess::allows('commission', 'search')) {
                abort(403);
            }
            $this->resetPage();
        }
    }

    public function openCreate(): void
    {
        $this->authorizeChange();
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorizeChange();
        $rule = CommissionRule::query()->findOrFail($id);
        $this->editingId = $rule->id;
        $this->name = $rule->name;
        $this->vendorId = (string) ($rule->vendor_id ?? '');
        $this->merchantId = (string) ($rule->merchant_id ?? '');
        $this->service = (string) $rule->service;
        $this->type = (string) $rule->type;
        $this->calcType = $rule->calc_type;
        $this->value = (string) $rule->value;
        $this->effectiveFrom = $rule->effective_from?->format('Y-m-d\TH:i') ?? '';
        $this->effectiveTo = $rule->effective_to?->format('Y-m-d\TH:i') ?? '';
        $this->priority = $rule->priority === null ? '' : (string) $rule->priority;
        $this->status = $rule->status;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorizeChange();

        $this->validate([
            'name' => 'required|string|max:120',
            'vendorId' => 'nullable|exists:vendors,id',
            'merchantId' => 'nullable|exists:merchants,id',
            'service' => 'nullable|string|max:40',
            'type' => 'nullable|string|max:40',
            'calcType' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0'.($this->calcType === 'percentage' ? '|max:100' : ''),
            'effectiveFrom' => 'nullable|date',
            'effectiveTo' => 'nullable|date|after_or_equal:effectiveFrom',
            'priority' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
        ]);

        if ($this->merchantId !== '' && $this->vendorId !== '') {
            $owns = Merchant::query()->whereKey($this->merchantId)->where('vendor_id', $this->vendorId)->exists();
            if (! $owns) {
                $this->addError('merchantId', 'Merchant must belong to the selected vendor.');

                return;
            }
        }

        if ($this->merchantId !== '' && $this->vendorId === '') {
            $this->addError('vendorId', 'Select the vendor that owns this merchant.');

            return;
        }

        $payload = [
            'name' => $this->name,
            'vendor_id' => $this->vendorId !== '' ? $this->vendorId : null,
            'merchant_id' => $this->merchantId !== '' ? $this->merchantId : null,
            'service' => $this->service !== '' ? strtolower($this->service) : null,
            'type' => $this->type !== '' ? strtolower($this->type) : null,
            'calc_type' => $this->calcType,
            'value' => $this->value,
            'status' => $this->status,
            'effective_from' => $this->effectiveFrom !== '' ? $this->effectiveFrom : null,
            'effective_to' => $this->effectiveTo !== '' ? $this->effectiveTo : null,
            'priority' => $this->priority !== '' ? (int) $this->priority : null,
            'updated_by' => Auth::guard('admin')->id(),
        ];

        if ($this->editingId) {
            CommissionRule::query()->findOrFail($this->editingId)->update($payload);
        } else {
            $payload['created_by'] = Auth::guard('admin')->id();
            CommissionRule::query()->create($payload);
        }

        $this->showModal = false;
        session()->flash('success', 'Commission rule saved.');
    }

    public function toggle(int $id): void
    {
        $this->authorizeChange();
        $rule = CommissionRule::query()->findOrFail($id);
        $rule->update([
            'status' => $rule->status === 'active' ? 'inactive' : 'active',
            'updated_by' => Auth::guard('admin')->id(),
        ]);
    }

    public function render()
    {
        $rules = CommissionRule::query()
            ->with(['vendor:id,business_name', 'merchant:id,code,first_name,last_name'])
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->filterVendor !== '', fn ($q) => $q->where('vendor_id', $this->filterVendor))
            ->when($this->filterMerchant !== '', fn ($q) => $q->where('merchant_id', $this->filterMerchant))
            ->when($this->filterService !== '', fn ($q) => $q->where('service', $this->filterService))
            ->when($this->filterType !== '', fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.admin.commission-rules', [
            'rules' => $rules,
            'vendors' => Vendor::query()->orderBy('business_name')->get(['id', 'business_name']),
            'merchants' => Merchant::query()->when($this->vendorId !== '', fn ($q) => $q->where('vendor_id', $this->vendorId))->orderBy('code')->get(['id', 'code', 'vendor_id']),
            'filterMerchants' => Merchant::query()->orderBy('code')->get(['id', 'code']),
            'canChange' => AdminAccess::allows('commission', 'change_commission'),
        ])->layout('layouts.admin', ['title' => 'Commission Rules']);
    }

    private function authorizeView(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('commission')) {
            abort(403);
        }
    }

    private function authorizeChange(): void
    {
        if (! AdminAccess::allows('commission', 'change_commission')) {
            abort(403);
        }
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'vendorId', 'merchantId', 'service', 'type', 'effectiveFrom', 'effectiveTo', 'priority']);
        $this->calcType = 'percentage';
        $this->value = '0';
        $this->status = 'active';
        $this->resetValidation();
    }
}
