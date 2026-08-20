<?php

namespace App\Livewire\Concerns;

use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

trait ManagesVendorRegistration
{
    public bool $kycIsAdmin = true;

    public bool $kycLocked = false;

    protected function kycFillRule(): string
    {
        return $this->kycIsAdmin ? 'nullable' : 'required';
    }

    protected function kycNumberRules(string $type = 'numeric'): array
    {
        $rules = [
            function (string $attribute, mixed $value, \Closure $fail) {
                if ($this->kycIsAdmin) {
                    return;
                }

                if ($value === null || $value === '') {
                    $fail($this->attributeLabel($attribute).' is required.');
                }
            },
            $type,
            'min:0',
        ];

        if ($this->kycIsAdmin) {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    protected function attributeLabel(string $attribute): string
    {
        $attrs = $this->validationAttributes();

        if (isset($attrs[$attribute])) {
            return $attrs[$attribute];
        }

        $wildcard = preg_replace('/\.\d+\./', '.*.', $attribute);

        return $attrs[$wildcard] ?? str_replace('_', ' ', $attribute);
    }

    protected function kycDateRules(bool $allowFuture = false): array
    {
        $rules = [
            $this->kycFillRule(),
            'date',
        ];

        if (! $allowFuture) {
            $rules[] = 'before_or_equal:today';
            $rules[] = 'after_or_equal:1900-01-01';
        }

        return $rules;
    }

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

    protected function initializeBusinessPlan(): void
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
        if ($this->kycLocked) {
            if ($this->step < 7) {
                $this->step++;
                $this->loadStepData($this->step);
            }

            return;
        }

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
                $this->kycFillRule(),
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
                $this->kycFillRule(),
                'string',
                'max:255',
            ],

            'registration_number' => [
                $this->kycFillRule(),
                'string',
                'max:255',
            ],

            'tax_identification' => [
                $this->kycFillRule(),
                'string',
                'max:255',
            ],

            'rbi_regulated' => [
                $this->kycIsAdmin ? 'nullable' : 'required',
                Rule::in($this->kycIsAdmin ? ['', 'yes', 'no'] : ['yes', 'no']),
            ],

            'incorporation_year' => [
                $this->kycFillRule(),
                'integer',
                'min:1800',
                'max:' . now()->year,
            ],

            'merchant_acquiring_years' => [
                $this->kycFillRule(),
                'numeric',
                'min:0',
            ],

            'additional_licenses' => [
                $this->kycFillRule(),
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
        if ($this->kycLocked) {
            return;
        }

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
                'gte:20',
                'lte:100',
            ],

            'promoters.*.pan' => [
                $this->kycFillRule(),
                'string',
                'max:30',
            ],

            'promoters.*.dob' => [
                $this->kycFillRule(),
                'date',
                'before_or_equal:today',
                'after_or_equal:1900-01-01',
            ],

            'promoters.*.address' => [
                $this->kycFillRule(),
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
        if ($this->kycLocked) {
            return;
        }

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
                    'full_name' => $promoter['name'],
                    'shareholding_percentage' =>
                        $promoter['share_percentage'],

                    'pan_card_no' =>
                        $promoter['pan'] ?: null,

                    'date_of_birth' =>
                        $promoter['dob'] ?: null,

                    'official_address' =>
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
                $this->kycFillRule(),
                'string',
                'max:30',
            ],

            'directors.*.dob' => $this->kycDateRules(),

            'directors.*.address' => [
                $this->kycFillRule(),
                'string',
                'max:2000',
            ],

            'profile_experience' => [
                $this->kycFillRule(),
                'string',
                'max:5000',
            ],

            'total_employees' => $this->kycNumberRules('integer'),
            'technology_employees' => $this->kycNumberRules('integer'),
            'sales_employees' => $this->kycNumberRules('integer'),
            'support_employees' => $this->kycNumberRules('integer'),
            'admin_finance_hr_employees' => $this->kycNumberRules('integer'),

            'processing_systems' => [
                $this->kycFillRule(),
                'string',
                'max:5000',
            ],

            'applications' => [
                $this->kycFillRule(),
                'string',
                'max:5000',
            ],

            'database' => [
                $this->kycFillRule(),
                'string',
                'max:5000',
            ],

            'switch' => [
                $this->kycFillRule(),
                'string',
                'max:5000',
            ],

            'terminals' => [
                $this->kycFillRule(),
                'string',
                'max:5000',
            ],

            'fraud_risk_systems' => [
                $this->kycFillRule(),
                'string',
                'max:5000',
            ],

            'merchant_agent_management_systems' => [
                $this->kycFillRule(),
                'string',
                'max:5000',
            ],

            'merchant_agent_portal' => [
                $this->kycFillRule(),
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
        if ($this->kycLocked) {
            return;
        }

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

            'business_plan.*.customers' => $this->kycNumberRules('integer'),
            'business_plan.*.transactions' => $this->kycNumberRules('integer'),
            'business_plan.*.volume' => $this->kycNumberRules('numeric'),
        ];
    }

    public function saveStep5(): void
    {
        if ($this->kycLocked) {
            return;
        }

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
        $fill = $this->kycFillRule();

        return [
            'ca_name' => [$fill, 'string', 'max:255'],
            'ca_constitution' => [$fill, 'string', 'max:255'],
            'ca_incorporation_date' => $this->kycDateRules(),
            'networth' => $this->kycNumberRules('numeric'),
            'credit_rating' => [$fill, 'string', 'max:255'],
            'dealing_with_bank_since' => [$fill, 'string', 'max:255'],
            'contract_expiry_date' => [$fill, 'date'],
            'engagement_scope' => [$fill, 'string', 'max:10000'],
            'open_risk_issues' => [$fill, 'string', 'max:10000'],
            'documentation_status' => [$fill, 'string', 'max:5000'],
            'conflict_of_interest' => [$fill, 'string', 'max:5000'],
            'terminated_or_penalties' => [$fill, 'string', 'max:5000'],
            'rbi_defaulter' => [$fill, 'string', 'max:5000'],
            'recommendations' => [$fill, 'string', 'max:10000'],
        ];
    }

    public function saveStep6(): void
    {
        if ($this->kycLocked) {
            return;
        }

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

    protected function loadStepData(int $step): void
    {
        if (!$this->vendorId) {
            return;
        }

        $vendor = Vendor::find($this->vendorId);

        if (!$vendor) {
            return;
        }

        if ($step === 7) {
            foreach ([1, 2, 3, 4, 5, 6] as $completedStep) {
                $this->loadStepData($completedStep);
            }

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
                                $promoter->full_name ?? '',

                            'share_percentage' =>
                                $promoter->shareholding_percentage ?? '',

                            'pan' =>
                                $promoter->pan_card_no ?? '',

                            'dob' =>
                                $promoter->date_of_birth
                                    ? $promoter->date_of_birth->format('Y-m-d')
                                    : '',

                            'address' =>
                                $promoter->official_address ?? '',
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

    protected function validationAttributes(): array
    {
        return [
            'business_name' => 'business name',
            'contact_name' => 'contact person',
            'entity_type' => 'type of entity',
            'registration_body' => 'registered with',
            'registration_number' => 'registration number',
            'tax_identification' => 'PAN / TIN',
            'rbi_regulated' => 'RBI regulation',
            'incorporation_year' => 'year of incorporation',
            'merchant_acquiring_years' => 'years in merchant acquiring',
            'additional_licenses' => 'additional licenses',
            'promoters.*.name' => 'promoter full name',
            'promoters.*.share_percentage' => 'shareholding %',
            'promoters.*.pan' => 'PAN card number',
            'promoters.*.dob' => 'date of birth',
            'promoters.*.address' => 'official address',
            'directors.*.name' => 'director full name',
            'directors.*.designation' => 'designation',
            'directors.*.pan' => 'PAN card number',
            'directors.*.dob' => 'date of birth',
            'directors.*.address' => 'official address',
            'profile_experience' => 'profile and past experience',
            'total_employees' => 'total employees',
            'technology_employees' => 'technology employees',
            'sales_employees' => 'sales employees',
            'support_employees' => 'support employees',
            'admin_finance_hr_employees' => 'admin / finance / HR employees',
            'processing_systems' => 'processing systems',
            'applications' => 'applications',
            'database' => 'database',
            'switch' => 'switch',
            'terminals' => 'terminals',
            'fraud_risk_systems' => 'fraud / risk systems',
            'merchant_agent_management_systems' => 'merchant / agent management systems',
            'merchant_agent_portal' => 'merchant / agent portal',
            'business_plan.*.customers' => 'customers',
            'business_plan.*.transactions' => 'transactions',
            'business_plan.*.volume' => 'volume',
            'ca_name' => 'name of CA',
            'ca_constitution' => 'constitution of CA',
            'ca_incorporation_date' => 'CA incorporation date',
            'networth' => 'networth',
            'credit_rating' => 'credit rating',
            'dealing_with_bank_since' => 'dealing with bank since',
            'contract_expiry_date' => 'contract expiry date',
            'engagement_scope' => 'engagement scope',
            'open_risk_issues' => 'open risk issues',
            'documentation_status' => 'documentation status',
            'conflict_of_interest' => 'conflict of interest',
            'terminated_or_penalties' => 'termination / penalties',
            'rbi_defaulter' => 'RBI defaulter status',
            'recommendations' => 'recommendations',
        ];
    }

    protected function messages(): array
    {
        $year = now()->year;

        return [
            'promoters.*.share_percentage.gte' => 'Shareholding must be at least 20%.',
            'promoters.*.share_percentage.lte' => 'Shareholding cannot be more than 100%.',
            'promoters.*.share_percentage.required' => 'Shareholding % is required.',
            'promoters.*.dob.required' => 'Date of birth is required.',
            'promoters.*.dob.before_or_equal' => 'Date of birth cannot be a future date.',
            'promoters.*.dob.after_or_equal' => 'Please enter a valid date of birth.',
            'directors.*.dob.required' => 'Date of birth is required.',
            'directors.*.dob.before_or_equal' => 'Date of birth cannot be a future date.',
            'ca_incorporation_date.before_or_equal' => 'CA incorporation date cannot be a future date.',
            'incorporation_year.max' => "Year of incorporation cannot be after {$year}.",
            'incorporation_year.min' => 'Please enter a valid year of incorporation.',
            'business_plan.*.customers.min' => 'Customers cannot be negative.',
            'business_plan.*.transactions.min' => 'Transactions cannot be negative.',
            'business_plan.*.volume.min' => 'Volume cannot be negative.',
        ];
    }

    public function incompleteKycSteps(?Vendor $vendor = null): array
    {
        $vendor = $vendor ?? ($this->vendorId ? Vendor::with([
            'legalDetails',
            'promoters',
            'directors',
            'teamItDetails',
            'businessPlans',
            'evaluation',
        ])->find($this->vendorId) : null);

        if (! $vendor) {
            return [1, 2, 3, 4, 5, 6];
        }

        $filled = static fn ($value) => filled($value) && trim((string) $value) !== '';
        $missing = [];

        if (! $filled($vendor->business_name)
            || ! $filled($vendor->contact_name)
            || ! $filled($vendor->email)
            || ! $filled($vendor->phone)
            || ! $filled($vendor->country)
            || ! $filled($vendor->address)) {
            $missing[] = 1;
        }

        $legal = $vendor->legalDetails;
        if (! $legal
            || ! $filled($legal->entity_type)
            || ! $filled($legal->registration_body)
            || ! $filled($legal->registration_number)
            || ! $filled($legal->tax_identification)
            || $legal->incorporation_year === null
            || $legal->merchant_acquiring_years === null) {
            $missing[] = 2;
        }

        $promoters = $vendor->promoters;
        if ($promoters->isEmpty() || $promoters->contains(function ($promoter) use ($filled) {
            return ! $filled($promoter->full_name)
                || $promoter->shareholding_percentage === null
                || ! $filled($promoter->pan_card_no)
                || ! $promoter->date_of_birth
                || ! $filled($promoter->official_address);
        })) {
            $missing[] = 3;
        }

        $team = $vendor->teamItDetails;
        $directors = $vendor->directors;
        if ($directors->isEmpty() || $directors->contains(function ($director) use ($filled) {
            return ! $filled($director->name)
                || ! $filled($director->designation)
                || ! $filled($director->pan_card_no)
                || ! $director->date_of_birth
                || ! $filled($director->official_address);
        }) || ! $team
            || ! $filled($team->processing_systems)
            || ! $filled($team->applications)
            || ! $filled($team->database_system)
            || ! $filled($team->switch_system)
            || ! $filled($team->terminals)
            || ! $filled($team->fraud_risk_management)
            || ! $filled($team->merchant_agent_management)
            || ! $filled($team->merchant_agent_portal)) {
            $missing[] = 4;
        }

        if ($vendor->businessPlans->count() < 36) {
            $missing[] = 5;
        }

        $evaluation = $vendor->evaluation;
        if (! $evaluation
            || ! $filled($evaluation->ca_name)
            || ! $filled($evaluation->ca_constitution)
            || ! $evaluation->ca_incorporation_date
            || $evaluation->networth === null
            || ! $filled($evaluation->credit_rating)
            || ! $filled($evaluation->dealing_with_bank_since)
            || ! $evaluation->contract_expiry_date
            || ! $filled($evaluation->engagement_scope)
            || ! $filled($evaluation->open_risk_issues)
            || ! $filled($evaluation->documentation_status)
            || ! $filled($evaluation->conflict_of_interest)
            || ! $filled($evaluation->terminated_or_penalties)
            || ! $filled($evaluation->rbi_defaulter)
            || ! $filled($evaluation->recommendations)) {
            $missing[] = 6;
        }

        return $missing;
    }

    public function kycCanSubmit(): bool
    {
        return $this->incompleteKycSteps() === [];
    }

    protected function getVendor(): Vendor
    {
        if (!$this->vendorId) {
            abort(404, 'Vendor registration session not found.');
        }

        return Vendor::findOrFail(
            $this->vendorId
        );
    }

    protected function advanceRegistrationStep(
        Vendor $vendor,
        int $nextStep
    ): void {
        if ((int) $vendor->registration_step < $nextStep) {
            $vendor->update([
                'registration_step' => $nextStep,
            ]);
        }
    }
}
