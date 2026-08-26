<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900"><?php echo e($vendor->business_name); ?></h1>
            <p class="mt-1 text-sm text-slate-500"><?php echo e($vendor->vendor_code); ?> · <?php echo e($vendor->pmt_code); ?></p>
        </div>
        <div class="flex items-center gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) $vendor->registration_step < 7): ?>
                <a href="<?php echo e(route('admin.vendors.create', $vendor)); ?>" class="fi-btn fi-btn-secondary">
                    Continue Registration
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <a href="<?php echo e(route('admin.vendors')); ?>" class="fi-btn fi-btn-secondary">
                ← Back to Vendors
            </a>
        </div>
    </div>

    <?php
        $statusCls = match($vendor->status) {
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'suspended' => 'bg-red-50 text-red-600 border-red-200',
            default => 'bg-slate-50 text-slate-600 border-slate-200',
        };
        $kycCls = match($vendor->kyc_status) {
            'verified' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-red-50 text-red-600 border-red-200',
            'submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };
        $kycLabel = match($vendor->kyc_status) {
            'verified' => 'KYC Approved',
            'rejected' => 'Rejected',
            'submitted' => 'Submitted',
            default => 'Pending',
        };
    ?>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">Status</p>
            <span class="mt-2 inline-block rounded-full border px-3 py-1 text-sm font-bold <?php echo e($statusCls); ?>"><?php echo e(ucfirst($vendor->status)); ?></span>
        </div>
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">KYC</p>
            <span class="mt-2 inline-block rounded-full border px-3 py-1 text-sm font-bold <?php echo e($kycCls); ?>"><?php echo e($kycLabel); ?></span>
        </div>
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">Wallet</p>
            <p class="mt-2 text-lg font-bold text-slate-900">₹<?php echo e(number_format((float) ($vendor->wallet->balance ?? 0), 2)); ?></p>
        </div>
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">API</p>
            <p class="mt-2 text-sm font-bold <?php echo e($vendor->api_enabled ? 'text-emerald-600' : 'text-slate-400'); ?>"><?php echo e($vendor->api_enabled ? 'Enabled' : 'Disabled'); ?></p>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reviewMessage): ?>
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <?php echo e($reviewMessage); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mb-5 flex gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50 p-1">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
            'kyc' => 'KYC',
            'profile' => 'Profile',
            'wallet' => 'Wallet',
            'transactions' => 'Transactions',
            'settlements' => 'Settlements',
            'beneficiaries' => 'Beneficiaries',
            'developer' => 'API / Developer',
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button" wire:click="setTab('<?php echo e($t); ?>')"
                    class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition
                           <?php echo e($tab === $t ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'); ?>">
                <?php echo e($lbl); ?>

            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php
        $kv = fn ($label, $value) => [
            'label' => $label,
            'value' => ($value === null || $value === '') ? '—' : $value,
        ];
    ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'kyc'): ?>
        <div class="space-y-5">
            <div class="fi-card p-6">
                <h3 class="text-base font-semibold text-slate-900">KYC Verification</h3>
                <p class="mt-1 text-sm text-slate-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->kyc_status === 'submitted'): ?>
                        Review all submitted steps, add a comment, then approve or reject.
                    <?php elseif($vendor->kyc_status === 'rejected'): ?>
                        KYC rejected. Approve / Reject buttons will appear again after the vendor resubmits.
                    <?php elseif($vendor->kyc_status === 'verified'): ?>
                        KYC is approved. Previous review comments are listed below.
                    <?php else: ?>
                        Waiting for the vendor to submit KYC.
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->kyc_status === 'submitted'): ?>
                    <div class="mt-5">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Comment</label>
                        <textarea wire:model="kycComment" rows="3" class="fi-input" placeholder="Add review comment for the vendor"></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kycComment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <button type="button" wire:click="approveKyc" class="fi-btn fi-btn-success">
                            Approve KYC
                        </button>
                        <button type="button" wire:click="rejectKyc" class="fi-btn fi-btn-danger">
                            Reject KYC
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-5 space-y-3">
                    <h4 class="text-sm font-semibold text-slate-800">Review comments</h4>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vendor->kycReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $reviewCls = match($review->action) {
                                'approved' => 'border-emerald-200 bg-emerald-50',
                                'rejected' => 'border-red-200 bg-red-50',
                                default => 'border-slate-200 bg-slate-50',
                            };
                            $reviewLabel = match($review->action) {
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'submitted' => 'Vendor resubmitted',
                                default => ucfirst($review->action),
                            };
                        ?>
                        <div class="rounded-xl border px-4 py-3 <?php echo e($reviewCls); ?>">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs font-semibold text-slate-700"><?php echo e($reviewLabel); ?></p>
                                <p class="text-xs text-slate-500"><?php echo e($review->created_at->format('d M Y H:i')); ?></p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->comment): ?>
                                <p class="mt-1.5 text-sm text-slate-800"><?php echo e($review->comment); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <p class="mt-1 text-xs text-slate-500">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->admin): ?>
                                    by <?php echo e($review->admin->name); ?>

                                <?php else: ?>
                                    by Vendor
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->kyc_comment): ?>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-sm text-slate-800"><?php echo e($vendor->kyc_comment); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->kyc_reviewed_at): ?>
                                    <p class="mt-1 text-xs text-slate-500">
                                        <?php echo e($vendor->kyc_reviewed_at->format('d M Y H:i')); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->kycReviewer): ?> · <?php echo e($vendor->kycReviewer->name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-slate-400">No review comments yet.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">1. Registration</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        $kv('Business Name', $vendor->business_name),
                        $kv('Contact Person', $vendor->contact_name),
                        $kv('Email', $vendor->email),
                        $kv('Phone', $vendor->phone),
                        $kv('Country', $vendor->country),
                        $kv('Address', $vendor->address),
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-400"><?php echo e($item['label']); ?></p>
                            <p class="mt-1 text-sm font-medium text-slate-800"><?php echo e($item['value']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">2. Legal Details</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->legalDetails): ?>
                    <?php $ld = $vendor->legalDetails; ?>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                            $kv('Entity Type', $ld->entity_type),
                            $kv('Registered With', $ld->registration_body),
                            $kv('Registration Number', $ld->registration_number),
                            $kv('PAN / TIN', $ld->tax_identification),
                            $kv('RBI Regulated', $ld->rbi_regulated ? 'Yes' : 'No'),
                            $kv('Incorporation Year', $ld->incorporation_year),
                            $kv('Merchant Acquiring Years', $ld->merchant_acquiring_years),
                            $kv('Additional Licenses', $ld->additional_licenses),
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs font-semibold text-slate-400"><?php echo e($item['label']); ?></p>
                                <p class="mt-1 text-sm font-medium text-slate-800"><?php echo e($item['value']); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-slate-400">Not submitted.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">3. Promoters / Shareholders</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vendor->promoters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-4 rounded-xl border border-slate-200 p-4 last:mb-0">
                        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Promoter <?php echo e($i + 1); ?></p>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                                $kv('Name', $p->full_name),
                                $kv('Share %', $p->shareholding_percentage),
                                $kv('PAN', $p->pan_card_no),
                                $kv('DOB', optional($p->date_of_birth)->format('d M Y')),
                                $kv('Address', $p->official_address),
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <p class="text-xs font-semibold text-slate-400"><?php echo e($item['label']); ?></p>
                                    <p class="mt-0.5 text-sm font-medium text-slate-800"><?php echo e($item['value']); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-400">Not submitted.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">4. Directors / KMP & IT</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vendor->directors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-4 rounded-xl border border-slate-200 p-4 last:mb-0">
                        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Director <?php echo e($i + 1); ?></p>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                                $kv('Name', $d->name),
                                $kv('Designation', $d->designation),
                                $kv('PAN', $d->pan_card_no),
                                $kv('DOB', optional($d->date_of_birth)->format('d M Y')),
                                $kv('Address', $d->official_address),
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <p class="text-xs font-semibold text-slate-400"><?php echo e($item['label']); ?></p>
                                    <p class="mt-0.5 text-sm font-medium text-slate-800"><?php echo e($item['value']); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-400">No directors submitted.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->teamItDetails): ?>
                    <?php $ti = $vendor->teamItDetails; ?>
                    <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [['Total', $ti->total_employees], ['Tech', $ti->technology_employees], ['Sales', $ti->sales_employees], ['Support', $ti->support_employees], ['Admin/HR', $ti->admin_finance_hr_employees]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$l, $v]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-center">
                                <p class="text-xl font-bold text-slate-900"><?php echo e($v ?? 0); ?></p>
                                <p class="text-xs text-slate-400"><?php echo e($l); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="fi-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-sm font-semibold text-slate-900">5. Business Plan</h3>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->businessPlans->isNotEmpty()): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Month</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Customers</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Transactions</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Volume</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendor->businessPlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-5 py-3 font-medium"><?php echo e($plan->month); ?></td>
                                        <td class="px-5 py-3"><?php echo e(number_format($plan->customer_registrations)); ?></td>
                                        <td class="px-5 py-3"><?php echo e(number_format($plan->transactions)); ?></td>
                                        <td class="px-5 py-3">₹<?php echo e(number_format($plan->total_volume)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="px-6 py-8 text-sm text-slate-400">Not submitted.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">6. Evaluation</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->evaluation): ?>
                    <?php $ev = $vendor->evaluation; ?>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                            $kv('CA Name', $ev->ca_name),
                            $kv('Constitution', $ev->ca_constitution),
                            $kv('Incorporation Date', optional($ev->ca_incorporation_date)->format('d M Y')),
                            $kv('Networth', $ev->networth),
                            $kv('Credit Rating', $ev->credit_rating),
                            $kv('Bank Since', $ev->dealing_with_bank_since),
                            $kv('Contract Expiry', optional($ev->contract_expiry_date)->format('d M Y')),
                            $kv('Engagement Scope', $ev->engagement_scope),
                            $kv('Open Risk Issues', $ev->open_risk_issues),
                            $kv('Documentation', $ev->documentation_status),
                            $kv('Conflict of Interest', $ev->conflict_of_interest),
                            $kv('Termination / Penalties', $ev->terminated_or_penalties),
                            $kv('RBI Defaulter', $ev->rbi_defaulter),
                            $kv('Recommendations', $ev->recommendations),
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs font-semibold text-slate-400"><?php echo e($item['label']); ?></p>
                                <p class="mt-1 text-sm font-medium text-slate-800"><?php echo e($item['value']); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-slate-400">Not submitted.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'profile'): ?>
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Business Profile</h3>
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        ['Business Name', $vendor->business_name],
                        ['Contact Person', $vendor->contact_name],
                        ['Email', $vendor->email],
                        ['Phone', $vendor->phone],
                        ['Country', $vendor->country],
                        ['Address', $vendor->address ?? '—'],
                        ['Vendor Code', $vendor->vendor_code],
                        ['PMT Code', $vendor->pmt_code ?? '—'],
                        ['Registered', $vendor->created_at->format('d M Y')],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$l, $v]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start justify-between gap-4">
                            <span class="text-xs text-slate-400"><?php echo e($l); ?></span>
                            <span class="text-right text-xs font-semibold text-slate-800"><?php echo e($v); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Commercial Settings</h3>
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        ['Transaction Limit', '₹'.number_format((float) $vendor->transaction_limit, 2)],
                        ['Commission Type', ucfirst($vendor->commission_type)],
                        ['Commission Value', $vendor->commission_value],
                        ['API Enabled', $vendor->api_enabled ? 'Yes' : 'No'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$l, $v]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-400"><?php echo e($l); ?></span>
                            <span class="text-xs font-semibold text-slate-800"><?php echo e($v); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'wallet'): ?>
        <div class="space-y-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="fi-card p-5">
                    <p class="text-xs text-slate-400">Available Balance</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">₹<?php echo e(number_format((float) ($vendor->wallet->balance ?? 0), 2)); ?></p>
                </div>
                <div class="fi-card p-5">
                    <p class="text-xs text-slate-400">Hold Balance</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">₹<?php echo e(number_format((float) ($vendor->wallet->hold_balance ?? 0), 2)); ?></p>
                </div>
            </div>

            <div class="fi-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-slate-900">Ledger</h3>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ledger && $ledger->isNotEmpty()): ?>
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Type</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ledger; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-5 py-3"><?php echo e($row->created_at->format('d M Y H:i')); ?></td>
                                    <td class="px-5 py-3"><?php echo e($row->type); ?></td>
                                    <td class="px-5 py-3">₹<?php echo e(number_format((float) $row->amount, 2)); ?></td>
                                    <td class="px-5 py-3"><?php echo e($row->reference ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                    <div class="px-5 py-3"><?php echo e($ledger->links()); ?></div>
                <?php else: ?>
                    <p class="px-6 py-8 text-sm text-slate-400">No ledger entries.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="fi-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-slate-900">Top-up Requests</h3>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($topups->isNotEmpty()): ?>
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Reference</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Mode</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $topups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-5 py-3"><?php echo e($req->reference); ?></td>
                                    <td class="px-5 py-3">₹<?php echo e(number_format((float) $req->amount, 2)); ?></td>
                                    <td class="px-5 py-3"><?php echo e($req->payment_mode); ?></td>
                                    <td class="px-5 py-3"><?php echo e(ucfirst($req->status)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                    <div class="px-5 py-3"><?php echo e($topups->links()); ?></div>
                <?php else: ?>
                    <p class="px-6 py-8 text-sm text-slate-400">No top-up requests.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'transactions'): ?>
        <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="fi-card p-4"><p class="text-xs text-slate-400">Total</p><p class="mt-1 text-xl font-bold"><?php echo e($txnSummary['total']); ?></p></div>
            <div class="fi-card p-4"><p class="text-xs text-slate-400">Success</p><p class="mt-1 text-xl font-bold text-emerald-600"><?php echo e($txnSummary['success']); ?></p></div>
            <div class="fi-card p-4"><p class="text-xs text-slate-400">Failed</p><p class="mt-1 text-xl font-bold text-red-600"><?php echo e($txnSummary['failed']); ?></p></div>
            <div class="fi-card p-4"><p class="text-xs text-slate-400">Volume</p><p class="mt-1 text-xl font-bold">₹<?php echo e(number_format($txnSummary['volume'], 2)); ?></p></div>
        </div>
        <div class="fi-card overflow-hidden">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transactions->isNotEmpty()): ?>
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Beneficiary</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-5 py-3"><?php echo e($txn->reference); ?></td>
                                <td class="px-5 py-3"><?php echo e($txn->beneficiary_name ?? '—'); ?></td>
                                <td class="px-5 py-3">₹<?php echo e(number_format((float) $txn->amount, 2)); ?></td>
                                <td class="px-5 py-3"><?php echo e(ucfirst($txn->status)); ?></td>
                                <td class="px-5 py-3"><?php echo e($txn->created_at->format('d M Y H:i')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
                <div class="px-5 py-3"><?php echo e($transactions->links()); ?></div>
            <?php else: ?>
                <p class="px-6 py-8 text-sm text-slate-400">No transactions.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'settlements'): ?>
        <div class="fi-card overflow-hidden">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settlements->isNotEmpty()): ?>
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Fee</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Net</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $settlements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-5 py-3"><?php echo e($row->reference); ?></td>
                                <td class="px-5 py-3">₹<?php echo e(number_format((float) $row->amount, 2)); ?></td>
                                <td class="px-5 py-3">₹<?php echo e(number_format((float) $row->fee, 2)); ?></td>
                                <td class="px-5 py-3">₹<?php echo e(number_format((float) $row->net_amount, 2)); ?></td>
                                <td class="px-5 py-3"><?php echo e(ucfirst($row->status)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
                <div class="px-5 py-3"><?php echo e($settlements->links()); ?></div>
            <?php else: ?>
                <p class="px-6 py-8 text-sm text-slate-400">No settlements.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'beneficiaries'): ?>
        <div class="fi-card overflow-hidden">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($beneficiaries->isNotEmpty()): ?>
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Account</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">IFSC</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Bank</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-400">Mobile</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $beneficiaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-5 py-3"><?php echo e($b->name); ?></td>
                                <td class="px-5 py-3"><?php echo e($b->account_number); ?></td>
                                <td class="px-5 py-3"><?php echo e($b->ifsc_code ?? '—'); ?></td>
                                <td class="px-5 py-3"><?php echo e($b->bank_name ?? '—'); ?></td>
                                <td class="px-5 py-3"><?php echo e($b->mobile ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
                <div class="px-5 py-3"><?php echo e($beneficiaries->links()); ?></div>
            <?php else: ?>
                <p class="px-6 py-8 text-sm text-slate-400">No beneficiaries.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'developer'): ?>
        <div class="space-y-5">
            <div class="fi-card p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-slate-900">API Access</h3>
                    <button type="button" wire:click="toggleApiAccess" class="fi-btn <?php echo e($vendor->api_enabled ? 'fi-btn-danger' : 'fi-btn-success'); ?> fi-btn-sm">
                        <?php echo e($vendor->api_enabled ? 'Disable API' : 'Enable API'); ?>

                    </button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vendor->apiCredential): ?>
                    <?php $c = $vendor->apiCredential; ?>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><span class="text-slate-500">API Key</span><span class="font-mono text-slate-800"><?php echo e($c->api_key); ?></span></div>
                        <div class="flex justify-between gap-4"><span class="text-slate-500">Webhook</span><span class="text-slate-800"><?php echo e($c->webhook_url ?: '—'); ?></span></div>
                        <div class="flex justify-between gap-4"><span class="text-slate-500">IP whitelist</span>
                            <span class="text-right text-slate-800">
                                <?php $ips = $c->ip_whitelist ?? []; ?>
                                <?php echo e($ips === [] ? 'Not set (API blocked)' : implode(', ', $ips)); ?>

                            </span>
                        </div>
                        <div class="flex justify-between gap-4"><span class="text-slate-500">Admin API flag</span><span class="font-semibold <?php echo e($vendor->api_enabled ? 'text-emerald-700' : 'text-red-600'); ?>"><?php echo e($vendor->api_enabled ? 'Enabled' : 'Disabled'); ?></span></div>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-slate-500">No API credentials generated.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="fi-card p-6">
                <h3 class="mb-3 text-sm font-semibold text-slate-900">Assign bank APIs</h3>
                <p class="mb-4 text-xs text-slate-500">Vendor will only see FinPay endpoints for the banks you assign here.</p>
                <div class="space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $allBanks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 text-sm hover:bg-slate-50">
                            <input type="checkbox" wire:model="assignedBankIds" value="<?php echo e($bank->id); ?>" class="rounded border-slate-300 text-blue-700">
                            <span>
                                <span class="font-semibold text-slate-800"><?php echo e($bank->name); ?></span>
                                <span class="font-mono text-xs text-slate-500"> <?php echo e($bank->code); ?></span>
                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-slate-500">No active banks. Add a bank from the Banks menu first.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allBanks->isNotEmpty()): ?>
                    <div class="mt-4 flex justify-end">
                        <button type="button" wire:click="saveAssignedBanks" class="fi-btn fi-btn-primary">Save bank assignment</button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="fi-card p-6">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Recent Webhook Logs</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $webhookLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-3 rounded-lg border border-slate-100 bg-slate-50 p-3 text-xs">
                        <p class="font-semibold text-slate-700"><?php echo e($log->event ?? $log->url ?? 'Webhook'); ?> · <?php echo e($log->status ?? ''); ?> · <?php echo e($log->created_at->format('d M Y H:i')); ?></p>
                        <p class="mt-1 text-slate-500"><?php echo e(\Illuminate\Support\Str::limit((string) ($log->response_body ?? ''), 180)); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-400">No webhook logs.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/vendors/show.blade.php ENDPATH**/ ?>