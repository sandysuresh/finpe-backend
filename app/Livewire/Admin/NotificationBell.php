<?php

namespace App\Livewire\Admin;

use App\Models\AdminNotification;
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
        AdminNotification::query()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function openNotification(int $id)
    {
        $notification = AdminNotification::findOrFail($id);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        $this->open = false;

        return $this->redirect($notification->action_url, navigate: true);
    }

    public function render()
    {
        $unread = AdminNotification::query()->whereNull('read_at')->count();

        $notifications = AdminNotification::query()
            ->with('vendor')
            ->latest()
            ->limit(20)
            ->get();

        return view('livewire.admin.notification-bell', compact('unread', 'notifications'));
    }
}
