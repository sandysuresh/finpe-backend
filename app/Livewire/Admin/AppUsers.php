<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AppUsers extends Component
{
    public string $section = 'all';

    public function mount(): void
    {
        if (! Auth::guard('admin')->user()?->hasModule('dashboard')) {
            abort(403);
        }

        $section = match (request()->route()?->getName()) {
            'admin.app-users.details' => 'details',
            'admin.app-users.kyc' => 'kyc',
            'admin.app-users.history' => 'history',
            'admin.app-users.activity' => 'activity',
            'admin.app-users.block' => 'block',
            default => 'all',
        };

        $this->section = $section;
    }

    public function render()
    {
        $current = $this->sections()[$this->section];

        return view('livewire.admin.app-users', [
            'current' => $current,
            'sections' => $this->sections(),
        ])->layout('layouts.admin', ['title' => $current['title']]);
    }

    private function sections(): array
    {
        return [
            'all' => [
                'title' => 'All Users',
                'text' => 'People who register from the app. Partners are not listed here.',
                'columns' => ['Name', 'Mobile', 'Email', 'KYC', 'Status', 'Registered'],
            ],
            'details' => [
                'title' => 'User Details',
                'text' => 'Open a registered app user to see profile, KYC, and account status.',
                'columns' => ['Name', 'Mobile', 'Email', 'City', 'Status'],
            ],
            'kyc' => [
                'title' => 'KYC Verification',
                'text' => 'App user KYC waiting for review. Partner KYC stays under Partners.',
                'columns' => ['User', 'Mobile', 'Document', 'Submitted', 'Status'],
            ],
            'history' => [
                'title' => 'User History',
                'text' => 'Transactions and account changes for an app user.',
                'columns' => ['User', 'Activity', 'Reference', 'Status', 'Time'],
            ],
            'activity' => [
                'title' => 'Login / Activity History',
                'text' => 'App login, device, and activity records.',
                'columns' => ['User', 'Activity', 'IP address', 'Time', 'Status'],
            ],
            'block' => [
                'title' => 'Block / Unblock User',
                'text' => 'Block or restore an app user after they have registered.',
                'columns' => ['Name', 'Mobile', 'Status', 'Last activity', 'Action'],
            ],
        ];
    }
}
