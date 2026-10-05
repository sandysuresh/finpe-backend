<?php

namespace App\Livewire\Vendor;

use App\Models\VendorNotification;
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
        $this->query()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function openNotification(int $id)
    {
        $notification = $this->query()->whereKey($id)->first();
        if (! $notification) {
            abort(404);
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
        $query = $this->query()->latest();
        $notifications = (clone $query)->limit(20)->get();
        $unread = (clone $query)->whereNull('read_at')->count();

        return view('livewire.vendor.notification-bell', compact('unread', 'notifications'));
    }

    private function query()
    {
        return VendorNotification::query()->where('vendor_id', Auth::guard('vendor')->id());
    }
}
