<?php
namespace App\Livewire\Vendor;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionReport extends Component {
    use WithPagination;

    public string $search     = '';
    public string $status     = '';
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $service    = '';

    public function resetFilters(): void {
        $this->reset(['search','status','dateFrom','dateTo','service']);
        $this->resetPage();
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function render() {
        $vendor = Auth::guard('vendor')->user();
        $transactions = $vendor->transactions()
            ->when($this->search, fn($q) => $q->where(function ($inner) {
                $inner->where('reference', 'like', '%'.$this->search.'%')
                    ->orWhere('beneficiary_name', 'like', '%'.$this->search.'%');
            }))
            ->when($this->status,   fn($q) => $q->where('status',   $this->status))
            ->when($this->service,  fn($q) => $q->where('service',  $this->service))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at','>=',$this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at','<=',$this->dateTo))
            ->latest()->paginate(15);

        $summary = [
            'total'   => $vendor->transactions()->count(),
            'success' => $vendor->transactions()->where('status','success')->count(),
            'failed'  => $vendor->transactions()->where('status','failed')->count(),
            'pending' => $vendor->transactions()->where('status','pending')->count(),
            'volume'  => number_format((float)$vendor->transactions()->where('status','success')->sum('amount'),2),
        ];

        return view('livewire.vendor.transaction-report', compact('transactions','summary'))
            ->layout('layouts.vendor', ['title' => 'Transaction Report']);
    }
}
