<?php

namespace App\Livewire\Admin;

use App\Models\AdminNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function markAllRead(): void
    {
        $admin = Auth::guard('admin')->user();
        $query = AdminNotification::query()->whereNull('read_at');

        if ($admin && ! $admin->isSuperAdmin()) {
            $allowedTypes = [];
            if ($admin->hasModule('vendors')) {
                $allowedTypes[] = 'kyc';
            }
            if ($admin->hasModule('wallet-requests')) {
                $allowedTypes[] = 'wallet';
            }
            if ($allowedTypes === []) {
                return;
            }
            $query->whereIn('type', $allowedTypes);
        }

        $query->update(['read_at' => now()]);
    }

    public function openNotification(int $id)
    {
        $notification = AdminNotification::findOrFail($id);
        $admin = Auth::guard('admin')->user();

        $needed = $notification->type === 'kyc' ? 'vendors' : 'wallet-requests';
        if (! $admin?->hasModule($needed)) {
            abort(403);
        }

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        $this->open = false;

        $url = (string) $notification->action_url;
        if ($url === '' || ! str_starts_with($url, '/') || str_starts_with($url, '//') || str_contains($url, '\\')) {
            abort(400);
        }

        return $this->redirect($url, navigate: true);
    }

    public function render()
    {
        $admin = Auth::guard('admin')->user();

        $query = AdminNotification::query()->with('vendor')->latest();

        if ($admin && ! $admin->isSuperAdmin()) {
            $allowedTypes = [];
            if ($admin->hasModule('vendors')) {
                $allowedTypes[] = 'kyc';
            }
            if ($admin->hasModule('wallet-requests')) {
                $allowedTypes[] = 'wallet';
            }

            if ($allowedTypes === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('type', $allowedTypes);
            }
        }

        $notifications = (clone $query)->limit(20)->get();
        $unread = (clone $query)->whereNull('read_at')->count();

        return view('livewire.admin.notification-bell', compact('unread', 'notifications'));
    }
}
