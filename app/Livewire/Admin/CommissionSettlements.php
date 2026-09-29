<?php

namespace App\Livewire\Admin;

use App\Models\CommissionEntry;
use App\Models\CommissionSettlement;
use App\Models\CommissionSettlementItem;
use App\Models\Vendor;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class CommissionSettlements extends Component
{
    use WithPagination;

    public string $vendorId = '';
    public string $periodStart = '';
    public string $periodEnd = '';
    public array $selected = [];
    public string $filterStatus = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('commission')) {
            abort(403);
        }
    }

    public function updatedFilterStatus(): void
    {
        if (! AdminAccess::allows('commission', 'search')) {
            abort(403);
        }
        $this->resetPage();
    }

    public function createDraft(): void
    {
        if (! AdminAccess::allows('commission', 'settlement')) {
            abort(403);
        }

        $this->validate([
            'vendorId' => 'required|exists:vendors,id',
            'periodStart' => 'required|date',
            'periodEnd' => 'required|date|after_or_equal:periodStart',
            'selected' => 'required|array|min:1',
        ]);

        $entries = $this->eligibleQuery()->whereIn('id', $this->selected)->get();
        if ($entries->count() !== count($this->selected)) {
            $this->addError('selected', 'One or more entries are not eligible.');

            return;
        }

        DB::transaction(function () use ($entries) {
            $settlement = CommissionSettlement::query()->create([
                'reference' => 'CMS-'.strtoupper(Str::random(10)),
                'vendor_id' => $this->vendorId,
                'period_start' => $this->periodStart,
                'period_end' => $this->periodEnd,
                'total_amount' => $entries->reduce(
                    fn (string $sum, CommissionEntry $entry) => bcadd($sum, (string) $entry->commission_amount, 2),
                    '0.00'
                ),
                'status' => 'draft',
                'created_by' => Auth::guard('admin')->id(),
                'wallet_ledger_id' => null,
            ]);

            foreach ($entries as $entry) {
                CommissionSettlementItem::query()->create([
                    'commission_settlement_id' => $settlement->id,
                    'commission_entry_id' => $entry->id,
                ]);
            }
        });

        $this->selected = [];
        session()->flash('success', 'Settlement draft created. Wallet was not changed.');
    }

    public function approve(int $id): void
    {
        if (! AdminAccess::allows('commission', 'approve')) {
            abort(403);
        }

        $settlement = CommissionSettlement::query()->findOrFail($id);
        if ($settlement->status !== 'draft') {
            return;
        }

        $settlement->update([
            'status' => 'approved',
            'approved_by' => Auth::guard('admin')->id(),
            'approved_at' => now(),
            'wallet_ledger_id' => null,
        ]);
    }

    public function reject(int $id): void
    {
        if (! AdminAccess::allows('commission', 'approve')) {
            abort(403);
        }

        $settlement = CommissionSettlement::query()->findOrFail($id);
        if (! in_array($settlement->status, ['draft', 'pending'], true)) {
            return;
        }

        $settlement->update([
            'status' => 'rejected',
            'rejected_by' => Auth::guard('admin')->id(),
            'rejected_at' => now(),
        ]);
    }

    public function render()
    {
        return view('livewire.admin.commission-settlements', [
            'settlements' => CommissionSettlement::query()
                ->with('vendor:id,business_name')
                ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
                ->orderByDesc('id')
                ->paginate(15),
            'eligible' => $this->vendorId !== '' && $this->periodStart !== '' && $this->periodEnd !== ''
                ? $this->eligibleQuery()->get()
                : collect(),
            'vendors' => Vendor::query()->orderBy('business_name')->get(['id', 'business_name']),
            'canSettle' => AdminAccess::allows('commission', 'settlement'),
            'canApprove' => AdminAccess::allows('commission', 'approve'),
        ])->layout('layouts.admin', ['title' => 'Commission Settlement']);
    }

    private function eligibleQuery()
    {
        return CommissionEntry::query()
            ->where('vendor_id', $this->vendorId)
            ->where('status', CommissionEntry::STATUS_RECORDED)
            ->whereDate('created_at', '>=', $this->periodStart)
            ->whereDate('created_at', '<=', $this->periodEnd)
            ->whereDoesntHave('settlementItem');
    }
}
