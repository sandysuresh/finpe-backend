<?php
namespace App\Livewire\Vendor;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SettlementReport extends Component {
    use WithPagination;

    public string $status   = '';
    public string $dateFrom = '';
    public string $dateTo   = '';

    public function resetFilters(): void {
        $this->reset(['status','dateFrom','dateTo']);
        $this->resetPage();
    }

    public function render() {
        $vendor = Auth::guard('vendor')->user();
        $settlements = $vendor->settlements()
            ->when($this->status,   fn($q) => $q->where('status',   $this->status))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at','>=',$this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at','<=',$this->dateTo))
            ->latest()->paginate(15);

        $summary = [
            'total_amount' => number_format((float)$vendor->settlements()->where('status','completed')->sum('net_amount'),2),
            'pending'      => $vendor->settlements()->where('status','pending')->count(),
            'completed'    => $vendor->settlements()->where('status','completed')->count(),
        ];

        return view('livewire.vendor.settlement-report', compact('settlements','summary'))
            ->layout('layouts.vendor', ['title' => 'Settlements']);
    }
}
