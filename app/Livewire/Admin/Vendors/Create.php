<?php

namespace App\Livewire\Admin\Vendors;

use App\Livewire\Concerns\ManagesVendorRegistration;
use App\Models\Vendor;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Create extends Component
{
    use ManagesVendorRegistration;

    public function mount(?Vendor $vendor = null): void
    {
        $this->initializeBusinessPlan();

        if (! $vendor || ! $vendor->exists) {
            $token = request()->query('vendor');
            if (is_string($token) && $token !== '') {
                $decodedId = \App\Support\UrlId::decode($token);
                $vendor = $decodedId ? Vendor::find($decodedId) : null;
            }
        }

        if ($vendor && $vendor->exists) {
            // Set vendorId FIRST — loadStepData() bails early without it.
            $this->vendorId = $vendor->id;
            $this->vendor   = $vendor;

            // registration_step stores the NEXT pending step (e.g. 2 means
            // Step 1 is done). Clamp to valid range 1-7.
            $savedStep  = (int) $vendor->registration_step;
            $this->step = ($savedStep >= 1 && $savedStep <= 7) ? $savedStep : 1;

            // Populate form fields for the restored step.
            $this->loadStepData($this->step);
        }
    }

    public function saveStep1(): void
    {
        $validated = $this->validate(
            $this->step1Rules()
        );

        DB::transaction(function () use ($validated) {

            if ($this->vendorId) {

                $vendor = Vendor::findOrFail($this->vendorId);

                $vendorData = [
                    'business_name' => $validated['business_name'],
                    'contact_name' => $validated['contact_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'] ?: null,
                    'country' => $validated['country'],
                    'status' => $validated['status'],
                ];

                if (!empty($validated['password'])) {
                    $vendorData['password'] = Hash::make($validated['password']);
                }

                $vendor->update($vendorData);

            } else {

                $vendor = Vendor::create([
                    'vendor_code' => $this->generateVendorCode(),

                    'business_name' => $validated['business_name'],
                    'contact_name' => $validated['contact_name'],
                    'email' => $validated['email'],

                    'pmt_code' => $this->generatePmtCode(),

                    'password' => $validated['password'],

                    'phone' => $validated['phone'],
                    'address' => $validated['address'] ?: null,
                    'country' => $validated['country'],

                    'kyc_status' => 'pending',
                    'status' => $validated['status'],

                    'api_enabled' => false,
                    'transaction_limit' => 0,
                    'commission_type' => 'percentage',
                    'commission_value' => 0,

                    /*
                     * 2 means Step 1 completed and
                     * Step 2 is next.
                     */
                    'registration_step' => 2,
                ]);

                Wallet::firstOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                    ],
                    [
                        'balance' => 0,
                        'hold_balance' => 0,
                    ]
                );

                $this->vendorId = $vendor->id;
            }

            $vendor->update([
                'registration_step' => max(
                    2,
                    (int) $vendor->registration_step
                ),
            ]);
        });

        $this->password = '';

        $this->step = 2;

        $this->loadStepData(2);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 7 - Final Submit
    |--------------------------------------------------------------------------
    */

    public function submitRegistration(): void
    {
        $vendor = $this->getVendor();

        /*
         * Make sure all previous steps exist.
         */
        if (!$vendor->legalDetails()->exists()) {
            $this->step = 2;
            return;
        }

        if (!$vendor->promoters()->exists()) {
            $this->step = 3;
            return;
        }

        if (!$vendor->directors()->exists()) {
            $this->step = 4;
            return;
        }

        if (!$vendor->teamItDetails()->exists()) {
            $this->step = 4;
            return;
        }

        if (!$vendor->businessPlans()->count()) {
            $this->step = 5;
            return;
        }

        if (!$vendor->evaluation()->exists()) {
            $this->step = 6;
            return;
        }

        DB::transaction(function () use ($vendor) {

            $vendor->update([
                'registration_step' => 7,
                'registration_completed_at' => now(),
            ]);

            Wallet::firstOrCreate(
                [
                    'vendor_id' => $vendor->id,
                ],
                [
                    'balance' => 0,
                    'hold_balance' => 0,
                ]
            );
        });

        session()->flash(
            'success',
            'Vendor registration completed successfully.'
        );

        $this->redirect(
            route('admin.vendors.show', $vendor),
            navigate: true
        );
    }

    private function generateVendorCode(): string
    {
        do {
            $code = 'VND' . strtoupper(
                substr(
                    bin2hex(random_bytes(5)),
                    0,
                    8
                )
            );
        } while (
            Vendor::where(
                'vendor_code',
                $code
            )->exists()
        );

        return $code;
    }

    private function generatePmtCode(): string
    {
        do {
            $code = 'PMT' . random_int(
                100000,
                999999
            );
        } while (
            Vendor::where(
                'pmt_code',
                $code
            )->exists()
        );

        return $code;
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.admin.vendors.create'
        )->layout('layouts.admin');
    }
}

