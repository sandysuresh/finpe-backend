<?php

namespace App\Livewire\Vendor;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Profile extends Component
{
    // ── Tabs ──────────────────────────────────────────────────────────────────
    public string $tab = 'profile';

    // ── Profile ───────────────────────────────────────────────────────────────
    public string $businessName    = '';
    public string $contactName     = '';
    public string $phone           = '';
    public string $address         = '';
    public string $country         = '';
    public string $successMsg      = '';
    public string $errorMsg        = '';

    // ── Password ──────────────────────────────────────────────────────────────
    public string $currentPassword  = '';
    public string $newPassword      = '';
    public string $confirmPassword  = '';

    // ── KYC Step viewer ───────────────────────────────────────────────────────
    public int    $kycStep          = 1;

    public function mount(): void
    {
        $v = Auth::guard('vendor')->user();
        $this->businessName = $v->business_name;
        $this->contactName  = $v->contact_name;
        $this->phone        = $v->phone;
        $this->address      = $v->address ?? '';
        $this->country      = $v->country;

        // Open vendor on the step they last completed
        $this->kycStep = max(1, min(7, (int) $v->registration_step));
    }

    public function goToKycStep(int $step): void
    {
        $vendor = Auth::guard('vendor')->user();
        $max    = (int) $vendor->registration_step;
        if ($step >= 1 && $step <= $max) {
            $this->kycStep = $step;
        }
    }

    public function saveProfile(): void
    {
        $this->successMsg = '';
        $this->errorMsg   = '';

        $this->validate([
            'businessName' => 'required|string|max:100',
            'contactName'  => 'required|string|max:100',
            'phone'        => 'required|string|max:20',
            'address'      => 'nullable|string|max:255',
            'country'      => 'required|string|max:100',
        ]);

        Auth::guard('vendor')->user()->update([
            'business_name' => $this->businessName,
            'contact_name'  => $this->contactName,
            'phone'         => $this->phone,
            'address'       => $this->address,
            'country'       => $this->country,
        ]);

        $this->successMsg = 'Profile updated successfully.';
    }

    public function changePassword(): void
    {
        $this->successMsg = '';
        $this->errorMsg   = '';

        $this->validate([
            'currentPassword' => 'required',
            'newPassword'     => 'required|min:8|same:confirmPassword',
        ]);

        $vendor = Auth::guard('vendor')->user();

        if (! Hash::check($this->currentPassword, $vendor->password)) {
            $this->errorMsg = 'Current password is incorrect.';
            return;
        }

        $vendor->update(['password' => Hash::make($this->newPassword)]);
        $this->reset(['currentPassword', 'newPassword', 'confirmPassword']);
        $this->successMsg = 'Password changed successfully.';
    }

    public function render()
    {
        $vendor = Auth::guard('vendor')->user()->load([
            'legalDetails',
            'promoters',
            'directors',
            'teamItDetails',
            'businessPlans',
            'evaluation',
        ]);

        return view('livewire.vendor.profile', compact('vendor'))
            ->layout('layouts.vendor', ['title' => 'Profile & KYC']);
    }
}
