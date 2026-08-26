<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Banks &amp; API Integration</h1>
            <p class="mt-1 text-sm text-slate-500">Add a bank, save the API they provide, then assign that bank API to vendors.</p>
        </div>
        <button type="button" wire:click="openCreate" class="fi-btn fi-btn-primary">
            <span class="text-lg leading-none">+</span>
            Add Bank
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
            ['1', 'Add bank', 'Name, code, sandbox/live'],
            ['2', 'Integrate bank APIs', 'Map the 10–12 APIs the bank gave you'],
            ['3', 'Assign vendors', 'Vendor sees FinPay docs for those APIs'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$n, $t, $d]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="fi-card p-4">
                <p class="text-xs font-bold text-blue-700">Step <?php echo e($n); ?></p>
                <p class="mt-1 text-sm font-semibold text-slate-900"><?php echo e($t); ?></p>
                <p class="mt-1 text-xs text-slate-500"><?php echo e($d); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <input wire:model.live.debounce.300ms="search" type="text" class="fi-input w-72 text-sm" placeholder="Search bank name or code...">
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Bank','Environment','Bank APIs','Vendors','Status','Last test','Action']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $banks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900"><?php echo e($bank->name); ?></p>
                                <p class="font-mono text-xs text-slate-500"><?php echo e($bank->code); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-semibold text-slate-700">
                                <?php echo e(strtoupper($bank->environment)); ?> · <?php echo e($bank->driver); ?>

                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600">
                                <p class="font-semibold text-slate-800"><?php echo e($bank->api_endpoints_count); ?> APIs</p>
                                <p class="mt-0.5 truncate text-[11px] text-slate-400"><?php echo e($bank->base_url ? $bank->base_url : 'Simulator / no URL'); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-900"><?php echo e($bank->vendors_count); ?></td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($bank->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'); ?>">
                                    <?php echo e($bank->is_active ? 'Active' : 'Inactive'); ?>

                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bank->is_default): ?>
                                    <span class="ml-1 text-[10px] font-semibold text-blue-700">Default</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                <?php echo e($bank->last_test_status ? ucfirst($bank->last_test_status) : '—'); ?>

                                <div class="text-[11px] text-slate-400"><?php echo e($bank->last_tested_at?->diffForHumans()); ?></div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="openApis(<?php echo e($bank->id); ?>)" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">Bank APIs</button>
                                    <button type="button" wire:click="openEdit(<?php echo e($bank->id); ?>)" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Credentials</button>
                                    <button type="button" wire:click="openAssign(<?php echo e($bank->id); ?>)" class="rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800">Assign vendors</button>
                                    <button type="button" wire:click="testConnection(<?php echo e($bank->id); ?>)" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Test</button>
                                    <button type="button" wire:click="toggleActive(<?php echo e($bank->id); ?>)" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        <?php echo e($bank->is_active ? 'Disable' : 'Enable'); ?>

                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-sm text-slate-500">No banks yet. Add the first bank to start API integration.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4"><?php echo e($banks->links()); ?></div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="fi-modal-overlay">
            <div class="fi-modal fi-modal-lg">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-900"><?php echo e($editingId ? 'Bank API integration' : 'Add bank'); ?></h2>
                    <button type="button" wire:click="$set('showModal', false)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100">✕</button>
                </div>
                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Bank name *</label>
                            <input type="text" wire:model="name" class="fi-input" placeholder="HDFC / IME / Nepal Bank">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Bank code *</label>
                            <input type="text" wire:model="code" class="fi-input uppercase" placeholder="HDFC">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Driver</label>
                            <select wire:model="driver" class="fi-input">
                                <option value="http">HTTP (live bank API)</option>
                                <option value="simulator">Simulator (testing)</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Environment</label>
                            <select wire:model="environment" class="fi-input">
                                <option value="sandbox">Sandbox</option>
                                <option value="live">Live</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Bank API base URL</label>
                            <input type="url" wire:model="base_url" class="fi-input" placeholder="https://api.bank.example.com">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['base_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <p class="text-sm font-semibold text-slate-900">Credentials given by the bank</p>
                    <p class="text-xs text-slate-500">Leave blank on edit to keep the current secret values.</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Username</label>
                            <input type="text" wire:model="username" class="fi-input" autocomplete="off">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                            <input type="password" wire:model="password" class="fi-input" autocomplete="new-password">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">API key</label>
                            <input type="text" wire:model="api_key" class="fi-input" autocomplete="off">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">API secret</label>
                            <input type="password" wire:model="api_secret" class="fi-input" autocomplete="new-password">
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Services *</label>
                        <div class="flex flex-wrap gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['imps'=>'IMPS','neft'=>'NEFT','rtgs'=>'RTGS']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="checkbox" wire:model="services" value="<?php echo e($key); ?>" class="rounded border-slate-300 text-blue-700">
                                    <?php echo e($label); ?>

                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['services'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-blue-700"> Active
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model="is_default" class="rounded border-slate-300 text-blue-700"> Default bank
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" wire:click="$set('showModal', false)" class="fi-btn fi-btn-secondary">Cancel</button>
                    <button type="button" wire:click="save" class="fi-btn fi-btn-primary">Save bank</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAssignModal): ?>
        <div class="fi-modal-overlay">
            <div class="fi-modal">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">Assign this bank API to vendors</h2>
                    <button type="button" wire:click="$set('showAssignModal', false)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100">✕</button>
                </div>
                <div class="space-y-4 p-6">
                    <p class="text-sm text-slate-600">Selected vendors will see FinPay API endpoints for this bank in their Developer panel. They never receive the bank’s own credentials.</p>
                    <input type="text" wire:model.live.debounce.300ms="assignSearch" class="fi-input text-sm" placeholder="Search vendor...">
                    <div class="max-h-80 space-y-2 overflow-y-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 text-sm hover:bg-slate-50">
                                <input type="checkbox" wire:model="assignedVendorIds" value="<?php echo e($vendor->id); ?>" class="rounded border-slate-300 text-blue-700">
                                <span>
                                    <span class="font-semibold text-slate-800"><?php echo e($vendor->business_name); ?></span>
                                    <span class="block text-xs text-slate-500"><?php echo e($vendor->vendor_code); ?></span>
                                </span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="py-6 text-center text-sm text-slate-500">No vendors found.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" wire:click="$set('showAssignModal', false)" class="fi-btn fi-btn-secondary">Cancel</button>
                    <button type="button" wire:click="saveAssignments" class="fi-btn fi-btn-primary">Save assignment</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showApisModal && $apisBank): ?>
        <div class="fi-modal-overlay">
            <div class="fi-modal fi-modal-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900"><?php echo e($apisBank->name); ?> APIs</h2>
                        <p class="text-xs text-slate-500">Integrate each API the bank provided. Vendors receive FinPay URLs, not bank credentials.</p>
                    </div>
                    <button type="button" wire:click="$set('showApisModal', false)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100">✕</button>
                </div>

                <div class="flex flex-wrap gap-2 border-b border-slate-100 px-6 py-3">
                    <button type="button" wire:click="openCreateEndpoint" class="fi-btn fi-btn-primary text-xs">+ Add API</button>
                    <button type="button" wire:click="seedMissingApis" class="fi-btn fi-btn-secondary text-xs">Load 12 templates</button>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEndpointForm): ?>
                    <div class="space-y-4 border-b border-slate-100 bg-slate-50 p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">API name *</label>
                                <input type="text" wire:model="endpointName" class="fi-input" placeholder="Create Payout">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['endpointName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Slug *</label>
                                <input type="text" wire:model="endpointSlug" class="fi-input font-mono text-sm" placeholder="payout">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['endpointSlug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <p class="mt-1 text-[11px] text-slate-500">Vendor URL: /api/v1/bank/<?php echo e($apisBank->code); ?>/<?php echo e($endpointSlug ?: 'slug'); ?></p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">HTTP method</label>
                                <select wire:model="endpointMethod" class="fi-input">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['GET','POST','PUT','PATCH','DELETE']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($m); ?>"><?php echo e($m); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Bank path</label>
                                <input type="text" wire:model="endpointBankPath" class="fi-input font-mono text-sm" placeholder="/payout">
                                <p class="mt-1 text-[11px] text-slate-500">Called on bank base URL. Hidden from vendors.</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Description</label>
                                <textarea wire:model="endpointDescription" rows="2" class="fi-input text-sm"></textarea>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-900">Request parameters</p>
                                <button type="button" wire:click="addRequestParam" class="text-xs font-semibold text-blue-700">+ Add field</button>
                            </div>
                            <div class="space-y-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $requestParams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="grid grid-cols-12 gap-2">
                                        <input type="text" wire:model="requestParams.<?php echo e($i); ?>.name" class="fi-input col-span-3 text-xs" placeholder="name">
                                        <select wire:model="requestParams.<?php echo e($i); ?>.type" class="fi-input col-span-2 text-xs">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['string','number','boolean','array','object']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($t); ?>"><?php echo e($t); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                        <label class="col-span-2 flex items-center gap-1 text-xs text-slate-600">
                                            <input type="checkbox" wire:model="requestParams.<?php echo e($i); ?>.required" class="rounded border-slate-300"> Required
                                        </label>
                                        <input type="text" wire:model="requestParams.<?php echo e($i); ?>.description" class="fi-input col-span-4 text-xs" placeholder="description">
                                        <button type="button" wire:click="removeRequestParam(<?php echo e($i); ?>)" class="col-span-1 text-xs text-red-600">✕</button>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <p class="text-xs text-slate-500">No request body fields (typical for GET).</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-900">Response parameters</p>
                                <button type="button" wire:click="addResponseParam" class="text-xs font-semibold text-blue-700">+ Add field</button>
                            </div>
                            <div class="space-y-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $responseParams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="grid grid-cols-12 gap-2">
                                        <input type="text" wire:model="responseParams.<?php echo e($i); ?>.name" class="fi-input col-span-3 text-xs" placeholder="data.status">
                                        <select wire:model="responseParams.<?php echo e($i); ?>.type" class="fi-input col-span-2 text-xs">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['string','number','boolean','array','object']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($t); ?>"><?php echo e($t); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                        <label class="col-span-2 flex items-center gap-1 text-xs text-slate-600">
                                            <input type="checkbox" wire:model="responseParams.<?php echo e($i); ?>.required"> Required
                                        </label>
                                        <input type="text" wire:model="responseParams.<?php echo e($i); ?>.description" class="fi-input col-span-4 text-xs" placeholder="description">
                                        <button type="button" wire:click="removeResponseParam(<?php echo e($i); ?>)" class="col-span-1 text-xs text-red-600">✕</button>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <p class="text-xs text-slate-500">Add fields vendors should expect in the JSON response.</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Sample request JSON</label>
                                <textarea wire:model="sampleRequestJson" rows="5" class="fi-input font-mono text-xs"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sampleRequestJson'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Sample response JSON</label>
                                <textarea wire:model="sampleResponseJson" rows="5" class="fi-input font-mono text-xs"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sampleResponseJson'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model="endpointActive" class="rounded border-slate-300 text-blue-700"> Active for vendors
                        </label>

                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="$set('showEndpointForm', false)" class="fi-btn fi-btn-secondary">Cancel</button>
                            <button type="button" wire:click="saveEndpoint" class="fi-btn fi-btn-primary">Save API</button>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Vendor API','Bank path','Request fields','Status','']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $apiEndpoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="text-sm font-semibold text-slate-900"><?php echo e($ep->name); ?></p>
                                        <p class="mt-0.5 font-mono text-[11px] text-slate-500"><?php echo e($ep->method); ?> /api/v1/bank/<?php echo e($apisBank->code); ?>/<?php echo e($ep->slug); ?></p>
                                        <p class="mt-1 text-xs text-slate-500"><?php echo e($ep->description); ?></p>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs text-slate-600"><?php echo e($ep->bank_path ?: '—'); ?></td>
                                    <td class="px-5 py-3 text-xs text-slate-600"><?php echo e(count($ep->request_params ?: [])); ?> in / <?php echo e(count($ep->response_params ?: [])); ?> out</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold <?php echo e($ep->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'); ?>">
                                            <?php echo e($ep->is_active ? 'Active' : 'Off'); ?>

                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3">
                                        <button type="button" wire:click="openEditEndpoint(<?php echo e($ep->id); ?>)" class="text-xs font-semibold text-blue-700">Edit</button>
                                        <button type="button" wire:click="toggleEndpoint(<?php echo e($ep->id); ?>)" class="ml-2 text-xs font-semibold text-slate-600">Toggle</button>
                                        <button type="button" wire:click="deleteEndpoint(<?php echo e($ep->id); ?>)" wire:confirm="Delete this API mapping?" class="ml-2 text-xs font-semibold text-red-600">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">No APIs yet. Load 12 templates or add each bank API manually.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/banks.blade.php ENDPATH**/ ?>