<?php

namespace App\Livewire\Admin;

use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ApiLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('banks')) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ApiLog::query()
            ->with('vendor:id,business_name,vendor_code')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('endpoint', 'like', $term)
                        ->orWhere('method', 'like', $term)
                        ->orWhere('ip_address', 'like', $term)
                        ->orWhereHas('vendor', function ($vendor) use ($term) {
                            $vendor->where('business_name', 'like', $term)
                                ->orWhere('vendor_code', 'like', $term);
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(20);

        return view('livewire.admin.api-logs', [
            'logs' => $logs,
        ])->layout('layouts.admin', ['title' => 'API Logs']);
    }
}
