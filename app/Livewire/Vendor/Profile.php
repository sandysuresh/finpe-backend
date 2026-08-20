<?php

namespace App\Livewire\Vendor;

use App\Livewire\Concerns\ManagesVendorRegistration;
use App\Models\VendorKycReview;
use App\Models\Wallet;
use App\Support\AdminNotify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    use ManagesVendorRegistration;

    public string $tab = 'profile';
    public string $successMsg = '';
    public string $errorMsg = '';

    public string $currentPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';

    public function mount(): void
    {
        $this->kycIsAdmin = false;

        $vendor = Auth::guard('vendor')->user();

        $this->vendorId = $vendor->id;
        $this->vendor = $vendor;

        $this->initializeBusinessPlan();

        $savedStep = (int) $vendor->registration_step;
        $this->step = ($savedStep >= 1 && $savedStep <= 7) ? $savedStep : 1;

        $this->loadStepData($this->step);
        $this->loadStepData(1);

        $this->kycLocked = in_array($vendor->kyc_status, ['submitted', 'verified'], true);
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 7) {
            return;
        }

        $this->step = $step;
        $this->loadStepData($step);
    }

    protected function step1Rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('vendors', 'email')->ignore($this->vendorId),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:2000'],
            'country' => ['required', 'string', 'max:100'],
        ];
    }

    public function saveStep1(): void
    {
        if ($this->kycLocked) {
            return;
        }

        $validated = $this->validate($this->step1Rules());

        $vendor = $this->getVendor();

        DB::transaction(function () use ($validated, $vendor) {
            $vendor->update([
                'business_name' => $validated['business_name'],
                'contact_name' => $validated['contact_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?: null,
                'country' => $validated['country'],
            ]);

            $this->advanceRegistrationStep($vendor, 2);
        });

        $this->step = 2;
        $this->loadStepData(2);
    }

    public function submitRegistration(): void
    {
        if ($this->kycLocked) {
            return;
        }

        $vendor = $this->getVendor()->load([
            'legalDetails',
            'promoters',
            'directors',
            'teamItDetails',
            'businessPlans',
            'evaluation',
        ]);

        $missing = $this->incompleteKycSteps($vendor);

        if ($missing !== []) {
            $labels = [
                1 => 'Registration',
                2 => 'Legal Details',
                3 => 'Promoters',
                4 => 'Directors & IT',
                5 => 'Business Plan',
                6 => 'Evaluation',
            ];
            $names = collect($missing)->map(fn ($n) => $labels[$n] ?? "Step {$n}")->implode(', ');
            $this->errorMsg = "KYC submit ke liye pehle yeh steps complete karein: {$names}.";
            $this->successMsg = '';
            $this->step = $missing[0];
            $this->loadStepData($this->step);

            return;
        }

        $wasRejected = $vendor->kyc_status === 'rejected';

        DB::transaction(function () use ($vendor, $wasRejected) {
            $vendor->update([
                'registration_step' => 7,
                'registration_completed_at' => now(),
                'kyc_status' => $vendor->kyc_status === 'verified'
                    ? 'verified'
                    : 'submitted',
            ]);

            Wallet::firstOrCreate(
                ['vendor_id' => $vendor->id],
                ['balance' => 0, 'hold_balance' => 0]
            );

            VendorKycReview::create([
                'vendor_id' => $vendor->id,
                'admin_id' => null,
                'action' => $wasRejected ? 'submitted' : 'submitted',
                'comment' => $wasRejected
                    ? 'Vendor resubmitted KYC after rejection.'
                    : 'Vendor submitted KYC for review.',
            ]);
        });

        $this->vendor = $vendor->fresh();
        AdminNotify::kycSubmitted($this->vendor, $wasRejected);
        $this->kycLocked = true;
        $this->successMsg = 'KYC submitted successfully. Admin will review your registration.';
        $this->tab = 'kyc';
        $this->step = 7;
        $this->loadStepData(7);
    }

    public function saveProfile(): void
    {
        $this->successMsg = '';
        $this->errorMsg = '';

        $this->validate([
            'business_name' => 'required|string|max:100',
            'contact_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'country' => 'required|string|max:100',
        ]);

        Auth::guard('vendor')->user()->update([
            'business_name' => $this->business_name,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'address' => $this->address,
            'country' => $this->country,
        ]);

        $this->successMsg = 'Profile updated successfully.';
    }

    public function changePassword(): void
    {
        $this->successMsg = '';
        $this->errorMsg = '';

        $this->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|same:confirmPassword',
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
        $this->vendor = Auth::guard('vendor')->user()->load([
            'legalDetails',
            'promoters',
            'directors',
            'teamItDetails',
            'businessPlans',
            'evaluation',
            'kycReviews.admin',
        ]);

        return view('livewire.vendor.profile')
            ->layout('layouts.vendor', ['title' => 'Profile & KYC']);
    }
}
