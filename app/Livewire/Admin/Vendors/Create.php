<?php

namespace App\Livewire\Admin\Vendors;

use App\Models\Vendor;
use App\Models\Wallet;
use App\Models\VendorDirector;
use App\Models\VendorBusinessPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class Create extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

    public int $step = 1;

    public ?int $vendorId = null;
    public ?Vendor $vendor = null;

    /*
    |--------------------------------------------------------------------------
    | Step 1 - Vendor Registration
    |--------------------------------------------------------------------------
    */

    public string $business_name = '';
    public string $contact_name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $address = '';
    public string $country = 'Nepal';
    public string $status = 'active';

    /*
    |--------------------------------------------------------------------------
    | Step 2 - Legal Details
    |--------------------------------------------------------------------------
    */

    public string $entity_type = '';
    public string $registration_body = '';
    public string $registration_number = '';
    public string $tax_identification = '';
    public string $rbi_regulated = '';
    public string $incorporation_year = '';
    public string $merchant_acquiring_years = '';
    public string $additional_licenses = '';

    /*
    |--------------------------------------------------------------------------
    | Step 3 - Promoters / Shareholders
    |--------------------------------------------------------------------------
    */

    public array $promoters = [
        [
            'name' => '',
            'share_percentage' => '',
            'pan' => '',
            'dob' => '',
            'address' => '',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Step 4 - Directors / KMP
    |--------------------------------------------------------------------------
    */

    public array $directors = [
        [
            'name' => '',
            'designation' => '',
            'pan' => '',
            'dob' => '',
            'address' => '',
        ],
    ];

    public string $profile_experience = '';

    /*
    |--------------------------------------------------------------------------
    | Step 4 - Team
    |--------------------------------------------------------------------------
    */

    public string $total_employees = '0';
    public string $technology_employees = '0';
    public string $sales_employees = '0';
    public string $support_employees = '0';
    public string $admin_finance_hr_employees = '0';

    /*
    |--------------------------------------------------------------------------
    | Step 4 - IT
    |--------------------------------------------------------------------------
    */

    public string $processing_systems = '';
    public string $applications = '';
    public string $database = '';
    public string $switch = '';
    public string $terminals = '';
    public string $fraud_risk_systems = '';
    public string $merchant_agent_management_systems = '';
    public string $merchant_agent_portal = '';

    /*
    |--------------------------------------------------------------------------
    | Step 5 - Business Plan
    |--------------------------------------------------------------------------
    */

    public array $business_plan = [];

    /*
    |--------------------------------------------------------------------------
    | Step 6 - Evaluation
    |--------------------------------------------------------------------------
    */

    public string $ca_name = '';
    public string $ca_constitution = '';
    public string $ca_incorporation_date = '';
    public string $networth = '';
    public string $credit_rating = '';
    public string $dealing_with_bank_since = '';
    public string $contract_expiry_date = '';
    public string $engagement_scope = '';
    public string $open_risk_issues = '';
    public string $documentation_status = '';
    public string $conflict_of_interest = '';
    public string $terminated_or_penalties = '';
    public string $rbi_defaulter = '';
    public string $recommendations = '';

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(?Vendor $vendor = null): void
    {
        $this->initializeBusinessPlan();

        // Support both route-model binding (/vendors/create/1)
        // and query-string (/vendors/create?vendor=1).
        if (!$vendor || !$vendor->exists) {
            $vendorId = request()->query('vendor');
            if ($vendorId) {
                $vendor = Vendor::find($vendorId);
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

    private function getPendingStep(): int
    {
        // Step 1: Basic registration
        if (empty($this->vendor->business_name)
            || empty($this->vendor->contact_name)
            || empty($this->vendor->email)
            || empty($this->vendor->phone)) {
            return 1;
        }

        // Step 2: Legal details
        if (!$this->vendor->legalDetails) {
            return 2;
        }

        // Step 3: Promoters / Shareholders
        if (!$this->vendor->promoters()->exists()) {
            return 3;
        }

        // Step 4: Directors / IT
        if (!$this->vendor->directors()->exists()) {
            return 4;
        }

        // Step 5: Business plan
        if (!$this->vendor->businessPlan) {
            return 5;
        }

        // Step 6: Evaluation
        if (!$this->vendor->evaluation) {
            return 6;
        }

        // Step 7: Review — everything completed
        return 7;
    }

    /*
    |--------------------------------------------------------------------------
    | Business Plan
    |--------------------------------------------------------------------------
    */

    private function initializeBusinessPlan(): void
    {
        if (!empty($this->business_plan)) {
            return;
        }

        $start = now()->startOfMonth();

        for ($i = 0; $i < 36; $i++) {
            $date = $start->copy()->addMonths($i);

            $this->business_plan[] = [
                'month' => $date->format('M Y'),
                'customers' => '',
                'transactions' => '',
                'volume' => '',
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    public function nextStep(): void
    {
        match ($this->step) {
            1 => $this->saveStep1(),
            2 => $this->saveStep2(),
            3 => $this->saveStep3(),
            4 => $this->saveStep4(),
            5 => $this->saveStep5(),
            6 => $this->saveStep6(),
            7 => $this->submitRegistration(),
            default => null,
        };
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 7) {
            return;
        }

        /*
         * New vendor can only move to Step 1.
         * After Step 1 is saved, vendorId exists and
         * completed steps can be opened.
         */
        if (!$this->vendorId && $step > 1) {
            return;
        }

        /*
         * Don't allow jumping to an unfinished future step.
         */
        $vendor = $this->vendorId
            ? Vendor::find($this->vendorId)
            : null;

        if ($vendor && $step > (int) $vendor->registration_step) {
            return;
        }

        $this->step = $step;

        $this->loadStepData($step);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 1
    |--------------------------------------------------------------------------
    */

    protected function step1Rules(): array
    {
        return [
            'business_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('vendors', 'email')
                    ->ignore($this->vendorId),
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],

            'password' => [
                $this->vendorId ? 'nullable' : 'required',
                'string',
                'min:8',
            ],

            'address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'country' => [
                'required',
                'string',
                'max:100',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'suspended',
                ]),
            ],
        ];
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
    | Step 2 - Legal Details
    |--------------------------------------------------------------------------
    */

    protected function step2Rules(): array
    {
        return [
            'entity_type' => [
                'required',
                'string',
                'max:100',
            ],

            'registration_body' => [
                'nullable',
                'string',
                'max:255',
            ],

            'registration_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tax_identification' => [
                'nullable',
                'string',
                'max:255',
            ],

            'rbi_regulated' => [
                'nullable',
                Rule::in([
                    '',
                    'yes',
                    'no',
                ]),
            ],

            'incorporation_year' => [
                'nullable',
                'integer',
                'min:1800',
                'max:' . now()->year,
            ],

            'merchant_acquiring_years' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'additional_licenses' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'address' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function saveStep2(): void
    {
        $validated = $this->validate(
            $this->step2Rules()
        );

        $vendor = $this->getVendor();

        DB::transaction(function () use ($validated, $vendor) {

            $vendor->update([
                'address' => $validated['address'] ?: null,
            ]);

            $vendor->legalDetails()->updateOrCreate(
                [
                    'vendor_id' => $vendor->id,
                ],
                [
                    'entity_type' => $validated['entity_type'],
                    'registration_body' =>
                        $validated['registration_body'] ?: null,

                    'registration_number' =>
                        $validated['registration_number'] ?: null,

                    'tax_identification' =>
                        $validated['tax_identification'] ?: null,

                    'rbi_regulated' =>
                        $validated['rbi_regulated'] === 'yes',

                    'incorporation_year' =>
                        $validated['incorporation_year'] ?: null,

                    'merchant_acquiring_years' =>
                        $validated['merchant_acquiring_years'] ?: null,

                    'additional_licenses' =>
                        $validated['additional_licenses'] ?: null,
                ]
            );

            $this->advanceRegistrationStep(
                $vendor,
                3
            );
        });

        $this->step = 3;

        $this->loadStepData(3);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 3 - Promoters
    |--------------------------------------------------------------------------
    */

    protected function step3Rules(): array
    {
        return [
            'promoters' => [
                'required',
                'array',
                'min:1',
            ],

            'promoters.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'promoters.*.share_percentage' => [
                'required',
                'numeric',
                'min:20',
                'max:100',
            ],

            'promoters.*.pan' => [
                'nullable',
                'string',
                'max:30',
            ],

            'promoters.*.dob' => [
                'nullable',
                'date',
            ],

            'promoters.*.address' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function addPromoter(): void
    {
        $this->promoters[] = [
            'name' => '',
            'share_percentage' => '',
            'pan' => '',
            'dob' => '',
            'address' => '',
        ];
    }

    public function removePromoter(int $index): void
    {
        if (count($this->promoters) <= 1) {
            return;
        }

        unset($this->promoters[$index]);

        $this->promoters = array_values(
            $this->promoters
        );
    }

    public function saveStep3(): void
    {
        $validated = $this->validate(
            $this->step3Rules()
        );

        $vendor = $this->getVendor();

        DB::transaction(function () use ($validated, $vendor) {

            /*
             * Replace current promoter records instead of
             * creating duplicates when user comes back.
             */
            $vendor->promoters()->delete();

            foreach ($validated['promoters'] as $promoter) {

                $vendor->promoters()->create([
                    'name' => $promoter['name'],
                    'share_percentage' =>
                        $promoter['share_percentage'],

                    'pan' =>
                        $promoter['pan'] ?: null,

                    'dob' =>
                        $promoter['dob'] ?: null,

                    'address' =>
                        $promoter['address'] ?: null,
                ]);
            }

            $this->advanceRegistrationStep(
                $vendor,
                4
            );
        });

        $this->step = 4;

        $this->loadStepData(4);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 4 - Directors + Team + IT
    |--------------------------------------------------------------------------
    */

    protected function step4Rules(): array
    {
        return [
            'directors' => [
                'required',
                'array',
                'min:1',
            ],

            'directors.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'directors.*.designation' => [
                'required',
                'string',
                'max:255',
            ],

            'directors.*.pan' => [
                'nullable',
                'string',
                'max:30',
            ],

            'directors.*.dob' => [
                'nullable',
                'date',
            ],

            'directors.*.address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'profile_experience' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'total_employees' => [
                'required',
                'integer',
                'min:0',
            ],

            'technology_employees' => [
                'required',
                'integer',
                'min:0',
            ],

            'sales_employees' => [
                'required',
                'integer',
                'min:0',
            ],

            'support_employees' => [
                'required',
                'integer',
                'min:0',
            ],

            'admin_finance_hr_employees' => [
                'required',
                'integer',
                'min:0',
            ],

            'processing_systems' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'applications' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'database' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'switch' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'terminals' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'fraud_risk_systems' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'merchant_agent_management_systems' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'merchant_agent_portal' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function addDirector(): void
    {
        $this->directors[] = [
            'name' => '',
            'designation' => '',
            'pan' => '',
            'dob' => '',
            'address' => '',
        ];
    }

    public function removeDirector(int $index): void
    {
        if (count($this->directors) <= 1) {
            return;
        }

        unset($this->directors[$index]);

        $this->directors = array_values(
            $this->directors
        );
    }

    public function saveStep4(): void
    {
        $validated = $this->validate(
            $this->step4Rules()
        );

        $vendor = $this->getVendor();

        DB::transaction(function () use ($validated, $vendor) {

            /*
             * Directors
             */
            $vendor->directors()->delete();

            foreach ($validated['directors'] as $director) {

                $vendor->directors()->create([
                    'name' => $director['name'],

                    'designation' =>
                        $director['designation'],

                    'pan_card_no' =>
                        $director['pan'] ?: null,

                    'date_of_birth' =>
                        $director['dob'] ?: null,

                    'official_address' =>
                        $director['address'] ?: null,

                    'profile_past_experience' =>
                        $validated['profile_experience']
                            ?: null,
                ]);
            }

            /*
             * Team + IT
             */
            $vendor->teamItDetails()->updateOrCreate(
                [
                    'vendor_id' => $vendor->id,
                ],
                [
                    'total_employees' =>
                        $validated['total_employees'],

                    'technology_employees' =>
                        $validated['technology_employees'],

                    'sales_employees' =>
                        $validated['sales_employees'],

                    'support_employees' =>
                        $validated['support_employees'],

                    'admin_finance_hr_employees' =>
                        $validated['admin_finance_hr_employees'],

                    'it_system_overview' => null,

                    'processing_systems' =>
                        $validated['processing_systems'] ?: null,

                    'applications' =>
                        $validated['applications'] ?: null,

                    'database_system' =>
                        $validated['database'] ?: null,

                    'switch_system' =>
                        $validated['switch'] ?: null,

                    'terminals' =>
                        $validated['terminals'] ?: null,

                    'fraud_risk_management' =>
                        $validated['fraud_risk_systems'] ?: null,

                    'merchant_agent_management' =>
                        $validated['merchant_agent_management_systems']
                            ?: null,

                    'merchant_agent_portal' =>
                        $validated['merchant_agent_portal'] ?: null,

                    'additional_systems' => null,
                ]
            );

            $this->advanceRegistrationStep(
                $vendor,
                5
            );
        });

        $this->step = 5;

        $this->loadStepData(5);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 5 - Business Plan
    |--------------------------------------------------------------------------
    */

    protected function step5Rules(): array
    {
        return [
            'business_plan' => [
                'required',
                'array',
                'size:36',
            ],

            'business_plan.*.month' => [
                'required',
                'string',
                'max:20',
            ],

            'business_plan.*.customers' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'business_plan.*.transactions' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'business_plan.*.volume' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function saveStep5(): void
    {
        $validated = $this->validate(
            $this->step5Rules()
        );

        $vendor = $this->getVendor();

        DB::transaction(function () use ($validated, $vendor) {

            /*
             * 36 monthly records.
             * Delete/recreate prevents duplicates if edited.
             */
            $vendor->businessPlans()->delete();

            foreach ($validated['business_plan'] as $plan) {

                $vendor->businessPlans()->create([
                    'month' => $plan['month'],

                    'customer_registrations' =>
                        (int) ($plan['customers'] ?? 0),

                    'transactions' =>
                        (int) ($plan['transactions'] ?? 0),

                    'total_volume' =>
                        (float) ($plan['volume'] ?? 0),
                ]);
            }

            $this->advanceRegistrationStep(
                $vendor,
                6
            );
        });

        $this->step = 6;

        $this->loadStepData(6);
    }

    /*
    |--------------------------------------------------------------------------
    | Step 6 - Evaluation
    |--------------------------------------------------------------------------
    */

    protected function step6Rules(): array
    {
        return [
            'ca_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'ca_constitution' => [
                'nullable',
                'string',
                'max:255',
            ],

            'ca_incorporation_date' => [
                'nullable',
                'date',
            ],

            'networth' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'credit_rating' => [
                'nullable',
                'string',
                'max:255',
            ],

            'dealing_with_bank_since' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contract_expiry_date' => [
                'nullable',
                'date',
            ],

            'engagement_scope' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'open_risk_issues' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'documentation_status' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'conflict_of_interest' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'terminated_or_penalties' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'rbi_defaulter' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'recommendations' => [
                'nullable',
                'string',
                'max:10000',
            ],
        ];
    }

    public function saveStep6(): void
    {
        $validated = $this->validate(
            $this->step6Rules()
        );

        $vendor = $this->getVendor();

        DB::transaction(function () use ($validated, $vendor) {

            $vendor->evaluation()->updateOrCreate(
                [
                    'vendor_id' => $vendor->id,
                ],
                [
                    'ca_name' =>
                        $validated['ca_name'] ?: null,

                    'ca_constitution' =>
                        $validated['ca_constitution'] ?: null,

                    'ca_incorporation_date' =>
                        $validated['ca_incorporation_date'] ?: null,

                    'networth' =>
                        $validated['networth'] ?: null,

                    'credit_rating' =>
                        $validated['credit_rating'] ?: null,

                    'dealing_with_bank_since' =>
                        $validated['dealing_with_bank_since'] ?: null,

                    'contract_expiry_date' =>
                        $validated['contract_expiry_date'] ?: null,

                    'engagement_scope' =>
                        $validated['engagement_scope'] ?: null,

                    'open_risk_issues' =>
                        $validated['open_risk_issues'] ?: null,

                    'documentation_status' =>
                        $validated['documentation_status'] ?: null,

                    'conflict_of_interest' =>
                        $validated['conflict_of_interest'] ?: null,

                    'terminated_or_penalties' =>
                        $validated['terminated_or_penalties'] ?: null,

                    'rbi_defaulter' =>
                        $validated['rbi_defaulter'] ?: null,

                    'recommendations' =>
                        $validated['recommendations'] ?: null,
                ]
            );

            $this->advanceRegistrationStep(
                $vendor,
                7
            );
        });

        $this->step = 7;

        $this->loadStepData(7);
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
            route('admin.vendors.show', [
                'vendor' => $vendor->id,
            ]),
            navigate: true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Data Loading
    |--------------------------------------------------------------------------
    */

    private function loadStepData(int $step): void
    {
        if (!$this->vendorId) {
            return;
        }

        $vendor = Vendor::find($this->vendorId);

        if (!$vendor) {
            return;
        }

        /*
         * Step 1
         */
        if ($step === 1) {

            $this->business_name = $vendor->business_name ?? '';
            $this->contact_name = $vendor->contact_name ?? '';
            $this->email = $vendor->email ?? '';
            $this->phone = $vendor->phone ?? '';
            $this->address = $vendor->address ?? '';
            $this->country = $vendor->country ?? 'Nepal';
            $this->status = $vendor->status ?? 'active';

            /*
             * Never load password from DB into public state.
             */
            $this->password = '';
        }

        /*
         * Step 2
         */
        if ($step === 2) {

            $legal = $vendor->legalDetails;

            if ($legal) {
                $this->entity_type =
                    $legal->entity_type ?? '';

                $this->registration_body =
                    $legal->registration_body ?? '';

                $this->registration_number =
                    $legal->registration_number ?? '';

                $this->tax_identification =
                    $legal->tax_identification ?? '';

                $this->rbi_regulated =
                    $legal->rbi_regulated ? 'yes' : 'no';

                $this->incorporation_year =
                    $legal->incorporation_year
                    ? (string) $legal->incorporation_year
                    : '';

                $this->merchant_acquiring_years =
                    $legal->merchant_acquiring_years !== null
                    ? (string) $legal->merchant_acquiring_years
                    : '';

                $this->additional_licenses =
                    $legal->additional_licenses ?? '';
            }

            $this->address = $vendor->address ?? '';
        }

        /*
         * Step 3
         */
        if ($step === 3) {

            $records = $vendor->promoters()
                ->orderBy('id')
                ->get();

            if ($records->isNotEmpty()) {

                $this->promoters = $records
                    ->map(function ($promoter) {
                        return [
                            'name' =>
                                $promoter->name ?? '',

                            'share_percentage' =>
                                $promoter->share_percentage ?? '',

                            'pan' =>
                                $promoter->pan ?? '',

                            'dob' =>
                                $promoter->dob
                                    ? $promoter->dob->format('Y-m-d')
                                    : '',

                            'address' =>
                                $promoter->address ?? '',
                        ];
                    })
                    ->toArray();
            }
        }

        /*
         * Step 4
         */
        if ($step === 4) {

            $records = $vendor->directors()
                ->orderBy('id')
                ->get();

            if ($records->isNotEmpty()) {

                $this->directors = $records
                    ->map(function ($director) {
                        return [
                            'name' =>
                                $director->name ?? '',

                            'designation' =>
                                $director->designation ?? '',

                            'pan' =>
                                $director->pan_card_no ?? '',

                            'dob' =>
                                $director->date_of_birth
                                    ? $director->date_of_birth->format('Y-m-d')
                                    : '',

                            'address' =>
                                $director->official_address ?? '',
                        ];
                    })
                    ->toArray();

                /*
                 * Current schema stores profile/experience
                 * on director records.
                 */
                $this->profile_experience =
                    $records->first()
                        ->profile_past_experience ?? '';
            }

            $team = $vendor->teamItDetails;

            if ($team) {

                $this->total_employees =
                    (string) $team->total_employees;

                $this->technology_employees =
                    (string) $team->technology_employees;

                $this->sales_employees =
                    (string) $team->sales_employees;

                $this->support_employees =
                    (string) $team->support_employees;

                $this->admin_finance_hr_employees =
                    (string) $team->admin_finance_hr_employees;

                $this->processing_systems =
                    $team->processing_systems ?? '';

                $this->applications =
                    $team->applications ?? '';

                $this->database =
                    $team->database_system ?? '';

                $this->switch =
                    $team->switch_system ?? '';

                $this->terminals =
                    $team->terminals ?? '';

                $this->fraud_risk_systems =
                    $team->fraud_risk_management ?? '';

                $this->merchant_agent_management_systems =
                    $team->merchant_agent_management ?? '';

                $this->merchant_agent_portal =
                    $team->merchant_agent_portal ?? '';
            }
        }

        /*
         * Step 5
         */
        if ($step === 5) {

            $plans = $vendor->businessPlans()
                ->orderBy('id')
                ->get();

            if ($plans->isNotEmpty()) {

                $this->business_plan = $plans
                    ->map(function ($plan) {
                        return [
                            'month' =>
                                $plan->month,

                            'customers' =>
                                $plan->customer_registrations,

                            'transactions' =>
                                $plan->transactions,

                            'volume' =>
                                $plan->total_volume,
                        ];
                    })
                    ->toArray();
            } else {
                $this->initializeBusinessPlan();
            }
        }

        /*
         * Step 6
         */
        if ($step === 6) {

            $evaluation = $vendor->evaluation;

            if ($evaluation) {

                $this->ca_name =
                    $evaluation->ca_name ?? '';

                $this->ca_constitution =
                    $evaluation->ca_constitution ?? '';

                $this->ca_incorporation_date =
                    $evaluation->ca_incorporation_date
                        ? $evaluation->ca_incorporation_date->format('Y-m-d')
                        : '';

                $this->networth =
                    $evaluation->networth !== null
                        ? (string) $evaluation->networth
                        : '';

                $this->credit_rating =
                    $evaluation->credit_rating ?? '';

                $this->dealing_with_bank_since =
                    $evaluation->dealing_with_bank_since ?? '';

                $this->contract_expiry_date =
                    $evaluation->contract_expiry_date
                        ? $evaluation->contract_expiry_date->format('Y-m-d')
                        : '';

                $this->engagement_scope =
                    $evaluation->engagement_scope ?? '';

                $this->open_risk_issues =
                    $evaluation->open_risk_issues ?? '';

                $this->documentation_status =
                    $evaluation->documentation_status ?? '';

                $this->conflict_of_interest =
                    $evaluation->conflict_of_interest ?? '';

                $this->terminated_or_penalties =
                    $evaluation->terminated_or_penalties ?? '';

                $this->rbi_defaulter =
                    $evaluation->rbi_defaulter ?? '';

                $this->recommendations =
                    $evaluation->recommendations ?? '';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function getVendor(): Vendor
    {
        if (!$this->vendorId) {
            abort(404, 'Vendor registration session not found.');
        }

        return Vendor::findOrFail(
            $this->vendorId
        );
    }

    private function advanceRegistrationStep(
        Vendor $vendor,
        int $nextStep
    ): void {
        if ((int) $vendor->registration_step < $nextStep) {
            $vendor->update([
                'registration_step' => $nextStep,
            ]);
        }
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