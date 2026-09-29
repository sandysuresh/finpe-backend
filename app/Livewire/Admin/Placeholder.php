<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Placeholder extends Component
{
    public function render()
    {
        $title = match (true) {
            request()->routeIs('admin.approval-workflow') => 'Approval Workflow',
            request()->routeIs('admin.system-settings') => 'System Settings',
            request()->routeIs('admin.settlements') => 'Settlements',
            default => 'System Settings',
        };

        return view('livewire.admin.placeholder', [
            'heading' => $title,
        ])->layout('layouts.admin', ['title' => $title]);
    }
}
