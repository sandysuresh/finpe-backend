<div class="space-y-6">

    
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900"><?php echo e($greeting); ?></h1>
            <p class="mt-1 text-sm text-slate-500">Here's your account overview for today.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('vendor.send-money')); ?>"
               class="flex items-center gap-2 rounded-xl bg-violet-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Send Money
            </a>
            <a href="<?php echo e(route('vendor.wallet')); ?>"
               class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Money
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="fi-card flex items-start justify-between p-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Available Balance</p>
                <h2 class="mt-3 text-[28px] font-bold leading-none text-slate-900">₹<?php echo e($availableBalance); ?></h2>
                <p class="mt-2 text-xs text-slate-400">Ready to transact</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-700 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
        </div>

        <div class="fi-card flex items-start justify-between p-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Hold Balance</p>
                <h2 class="mt-3 text-[28px] font-bold leading-none text-slate-900">₹<?php echo e($holdBalance); ?></h2>
                <p class="mt-2 text-xs text-slate-400">Pending settlement</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-600 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                </svg>
            </div>
        </div>

        <div class="fi-card flex items-start justify-between p-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Today's Transactions</p>
                <h2 class="mt-3 text-[28px] font-bold leading-none text-slate-900"><?php echo e($todayTotal); ?></h2>
                <p class="mt-2 text-xs text-slate-400">Volume: ₹<?php echo e($todayVolume); ?></p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-700 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
        </div>

        <div class="fi-card flex items-start justify-between p-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Success / Failed</p>
                <h2 class="mt-3 text-[28px] font-bold leading-none">
                    <span class="text-emerald-600"><?php echo e($todaySuccess); ?></span>
                    <span class="text-slate-400">/</span>
                    <span class="text-red-500"><?php echo e($todayFailed); ?></span>
                </h2>
                <p class="mt-2 text-xs text-slate-400"><?php echo e($todayPending); ?> pending today</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-700 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        <div class="fi-card lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Transaction Activity</h3>
                    <p class="mt-0.5 text-xs text-slate-400"><?php echo e($activity['from']); ?> – <?php echo e($activity['to']); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-600"></span>Success</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-amber-500"></span>Pending</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-red-600"></span>Failed</span>
                </div>
            </div>
            <div class="px-6 pb-6 pt-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity['count'] === 0): ?>
                    <div class="flex h-56 items-center justify-center text-sm text-slate-500">No transactions in this period.</div>
                <?php else: ?>
                    <?php
                        $plotHeight = 160;
                        $max = max($activity['max'], 1);
                    ?>
                    <div class="flex h-56 items-end gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activity['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $height = $day['total'] > 0 ? max(8, (int) round($day['total'] / $max * $plotHeight)) : 0;
                                $parts = [
                                    ['count' => $day['success'], 'class' => 'bg-emerald-600'],
                                    ['count' => $day['pending'], 'class' => 'bg-amber-500'],
                                    ['count' => $day['failed'], 'class' => 'bg-red-600'],
                                    ['count' => $day['other'], 'class' => 'bg-slate-400'],
                                ];
                            ?>
                            <div class="group flex min-w-0 flex-1 flex-col items-center justify-end">
                                <div class="relative flex w-full max-w-10 flex-col justify-end overflow-hidden rounded-t-md" style="height: <?php echo e($plotHeight); ?>px;">
                                    <div class="invisible absolute -top-8 left-1/2 z-10 -translate-x-1/2 whitespace-nowrap rounded bg-slate-900 px-2 py-1 text-[10px] text-white group-hover:visible">
                                        <?php echo e($day['title']); ?> · <?php echo e($day['total']); ?>

                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($height > 0): ?>
                                        <div class="flex w-full flex-col justify-end" style="height: <?php echo e($height); ?>px;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($part['count'] > 0): ?>
                                                    <div class="<?php echo e($part['class']); ?>" style="height: <?php echo e(round($part['count'] / $day['total'] * $height)); ?>px;"></div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <span class="mt-2 text-center text-[10px] font-medium text-slate-500"><?php echo e($day['label']); ?></span>
                                <span class="text-[10px] text-slate-400"><?php echo e($day['total']); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="fi-card">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-sm font-semibold text-slate-900">This Month</h3>
                <p class="mt-0.5 text-xs text-slate-400"><?php echo e(now()->format('F Y')); ?></p>
            </div>
            <div class="space-y-5 p-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                    ['Successful', $successRate, 'bg-emerald-500', 'text-emerald-600'],
                    ['Pending',    $pendingRate, 'bg-amber-500',   'text-amber-700'],
                    ['Failed',     $failedRate,  'bg-red-600',     'text-red-700'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $rate, $barCls, $textCls]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-600"><?php echo e($label); ?></span>
                            <span class="text-xs font-bold <?php echo e($textCls); ?>"><?php echo e($rate); ?>%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full <?php echo e($barCls); ?>" style="width:<?php echo e($rate); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Total Transactions</span>
                        <span class="text-sm font-bold text-slate-900"><?php echo e(number_format($monthTotal)); ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Total Volume</span>
                        <span class="text-sm font-bold text-slate-900">₹<?php echo e($monthVolume); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        
        <div class="fi-card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Recent Transactions</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Your latest payment activity</p>
                </div>
                <a href="<?php echo e(route('vendor.transactions')); ?>" class="text-xs font-semibold text-violet-600 hover:text-violet-700">View all →</a>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($recentTransactions)): ?>
                <div class="flex flex-col items-center justify-center py-14 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                        <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <p class="mt-3 text-sm font-medium text-slate-700">No transactions yet</p>
                    <a href="<?php echo e(route('vendor.send-money')); ?>" class="mt-4 rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white hover:bg-violet-700">
                        Send Money
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto fi-scroll">
                    <table class="min-w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Reference','Beneficiary','Amount','Service','Status','Date','']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400"><?php echo e($col); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $bc = match($tx['status']) { 'success'=>'bg-emerald-50 text-emerald-700','failed'=>'bg-red-50 text-red-600',default=>'bg-amber-50 text-amber-700' }; ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="whitespace-nowrap px-5 py-3.5 text-xs font-semibold text-slate-800"><?php echo e($tx['reference']); ?></td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-xs text-slate-600"><?php echo e($tx['beneficiary_name']); ?></td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-xs font-semibold text-slate-900">₹<?php echo e($tx['amount']); ?></td>
                                    <td class="whitespace-nowrap px-5 py-3.5">
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500"><?php echo e($tx['service']); ?></span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($bc); ?>"><?php echo e(ucfirst($tx['status'])); ?></span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-[11px] text-slate-400"><?php echo e($tx['date']); ?></td>
                                    <td class="whitespace-nowrap px-5 py-3.5"><?php echo $__env->make('livewire.vendor.partials.txn-actions', ['id' => $tx['id'], 'reference' => $tx['reference'], 'type' => $tx['raw_type']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="space-y-5">
            <div class="fi-card p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Quick Actions</h3>
                <div class="space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        ['Send Money',        route('vendor.send-money'),   'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
                        ['Wallet / Add Money',route('vendor.wallet'),       'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                        ['Transaction Report',route('vendor.transactions'), 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['API & Developer',   route('vendor.developer'),    'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $href, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($href); ?>"
                           class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icon); ?>"/>
                            </svg>
                            <?php echo e($label); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="fi-card p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Account</h3>
                <?php $v = auth('vendor')->user(); ?>
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        ['Vendor Code', $v->vendor_code],
                        ['PMT Code',    $v->pmt_code ?? '—'],
                        ['Email',       $v->email],
                        ['Phone',       $v->phone],
                        ['Country',     $v->country],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-400"><?php echo e($label); ?></span>
                            <span class="text-xs font-semibold text-slate-700"><?php echo e($value); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-400">KYC Status</span>
                            <?php $kycCls = match($v->kyc_status) { 'verified'=>'bg-emerald-50 text-emerald-700','rejected'=>'bg-red-50 text-red-600','submitted'=>'bg-blue-50 text-blue-700',default=>'bg-amber-50 text-amber-700' }; ?>
                            <a href="<?php echo e(route('vendor.profile')); ?>"
                               class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($kycCls); ?> hover:opacity-80">
                                <?php echo e($v->kyc_status === 'verified' ? 'KYC Approved' : ucfirst($v->kyc_status)); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/vendor/dashboard.blade.php ENDPATH**/ ?>