<?php

namespace App\Livewire\Admin;

use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterActor = '';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('users')) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterActor(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = AdminAuditLog::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('actor_name', 'like', $term)
                        ->orWhere('actor_email', 'like', $term)
                        ->orWhere('action', 'like', $term)
                        ->orWhere('subject_label', 'like', $term)
                        ->orWhere('old_value', 'like', $term)
                        ->orWhere('new_value', 'like', $term)
                        ->orWhere('ip_address', 'like', $term);
                });
            })
            ->when($this->filterStatus !== '', fn ($query) => $query->where('status', $this->filterStatus))
            ->when($this->filterActor !== '', fn ($query) => $query->where('actor_type', $this->filterActor))
            ->orderByDesc('id')
            ->paginate(20);

        return view('livewire.admin.audit-logs', [
            'logs' => $logs,
        ])->layout('layouts.admin', ['title' => 'Audit Logs']);
    }
}
