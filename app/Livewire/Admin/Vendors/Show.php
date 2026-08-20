<?php

namespace App\Livewire\Admin\Vendors;

use App\Models\Vendor;
use Livewire\Component;

class Show extends Component
{
    public Vendor $vendor;

    public function mount(Vendor $vendor): void
    {
        $this->vendor = $vendor->load([
            'legalDetails',
            'promoters',
            'directors',
            'teamItDetails',
            'businessPlans',
            'evaluation',
            'wallet',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.vendors.show', ['vendor' => $this->vendor])
            ->layout('layouts.admin');
    }
}
