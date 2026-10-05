<?php
    $icons = [
        'volume' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M7 8h10M7 12h10M9 16h6"/></svg>',
        'txn' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6h11M8 12h11M8 18h11M4 6h.01M4 12h.01M4 18h.01"/></svg>',
        'partners' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM20 19v-1a3.5 3.5 0 0 0-2.5-3.3M16.5 5.2a3 3 0 0 1 0 5.6"/></svg>',
        'kyc' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3"/><path d="M5 19a7 7 0 0 1 14 0"/></svg>',
        'merchants' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 10h16v9H4zM3 10l2-5h14l2 5M9 19v-4h6v4"/></svg>',
        'commission' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M16 7H10a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6H8"/></svg>',
        'settlement' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10h18M5 10V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v3M6 10v8h12v-8"/></svg>',
        'failed' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="m9 9 6 6M15 9l-6 6"/></svg>',
        'alert' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4M12 17h.01M10.3 4.8 2.8 18a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.8a2 2 0 0 0-3.4 0Z"/></svg>',
    ];
?>
<div class="dash-wrap">
<style>
.dash-wrap{height:calc(100vh - 110px);display:flex;flex-direction:column;overflow:hidden}
.dash-card,.dash-mini{background:#fff;border:1px solid #e6edf5;border-radius:14px;box-shadow:0 1px 2px rgba(15,23,42,.04);min-width:0}
.dash-card{display:flex;align-items:center;gap:12px;padding:14px 16px;border-top:3px solid #1d4ed8;text-decoration:none;color:inherit;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease}
.dash-card:hover,.dash-mini:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(15,23,42,.08)}
.dash-mini{text-decoration:none;color:inherit;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease}
.dash-card-blue{border-top-color:#1d4ed8}.dash-card-indigo{border-top-color:#4f46e5}.dash-card-emerald{border-top-color:#059669}.dash-card-amber{border-top-color:#d97706}.dash-card-violet{border-top-color:#7c3aed}.dash-card-red{border-top-color:#dc2626}.dash-card-slate{border-top-color:#64748b}
.dash-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#eff6ff;color:#1d4ed8}
.dash-icon svg,.dash-mini-icon svg{width:18px;height:18px}
.dash-card-indigo .dash-icon{background:#eef2ff;color:#4f46e5}
.dash-card-emerald .dash-icon{background:#ecfdf5;color:#059669}
.dash-card-amber .dash-icon{background:#fffbeb;color:#d97706}
.dash-label{font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-value{margin-top:2px;font-size:22px;line-height:1.2;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-hint{margin-top:2px;font-size:11px;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-mini{padding:12px 14px;border-top:3px solid #64748b}
.dash-mini-icon{width:22px;height:22px;color:#64748b;display:flex}
.dash-card-blue .dash-mini-icon{color:#1d4ed8}.dash-card-violet .dash-mini-icon{color:#7c3aed}.dash-card-emerald .dash-mini-icon{color:#059669}.dash-card-red .dash-mini-icon{color:#dc2626}.dash-card-amber .dash-mini-icon{color:#d97706}
.dash-mini-value{margin-top:8px;font-size:18px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
</style>
    <div class="mb-3 flex items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Dashboard</h1>
            <p class="text-xs text-slate-500">Partners, payouts, and items that need attention.</p>
        </div>
        <p class="text-xs font-medium text-slate-500"><?php echo e(now()->timezone(config('app.timezone'))->format('d M Y')); ?></p>
    </div>

    <div class="grid gap-3" style="grid-template-columns:repeat(4,minmax(0,1fr));">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $primary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($card['href']); ?>" class="dash-card dash-card-<?php echo e($card['tone']); ?>">
                <div class="dash-icon"><?php echo $icons[$card['icon']]; ?></div>
                <div class="min-w-0">
                    <p class="dash-label"><?php echo e($card['label']); ?></p>
                    <p class="dash-value"><?php echo e($card['value']); ?></p>
                    <p class="dash-hint"><?php echo e($card['hint']); ?></p>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mt-3 grid gap-3" style="grid-template-columns:repeat(6,minmax(0,1fr));">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $secondary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($card['href']); ?>" class="dash-mini dash-card-<?php echo e($card['tone']); ?>">
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-label"><?php echo e($card['label']); ?></p>
                    <span class="dash-mini-icon"><?php echo $icons[$card['icon']]; ?></span>
                </div>
                <p class="dash-mini-value"><?php echo e($card['value']); ?></p>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mt-3 grid min-h-0 flex-1 gap-3" style="grid-template-columns:minmax(0,2fr) minmax(0,1fr);">
        <div class="fi-card flex min-h-0 min-w-0 flex-col overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-900">Recent transactions</h2>
                <a href="<?php echo e(route('admin.transactions')); ?>" class="text-xs font-semibold text-blue-700">View all</a>
            </div>
            <div class="min-h-0 flex-1 overflow-hidden">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Reference','Partner','Payout Provider','Amount','Commission / Charge','Status','Time','']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-4 py-2 text-left text-[11px] font-semibold uppercase tracking-wider"><?php echo e($col); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentTransactions->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $tone = match($txn->status) {
                                    'success' => 'bg-emerald-50 text-emerald-700',
                                    'failed' => 'bg-red-50 text-red-700',
                                    default => 'bg-amber-50 text-amber-700',
                                };
                            ?>
                            <tr>
                                <td class="truncate px-4 py-2 font-mono text-xs font-semibold text-slate-800"><?php echo e($txn->reference); ?></td>
                                <td class="truncate px-4 py-2 text-sm text-slate-700"><?php echo e($txn->vendor->business_name ?? '—'); ?></td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm font-semibold text-slate-900"><?php echo e($txn->type === 'payout' ? (\App\Support\CommissionProviders::name($txn->payout_provider) ?: '—') : '—'); ?></td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm font-semibold text-slate-900">₹ <?php echo e(number_format((float) $txn->amount, 2)); ?></td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm font-semibold text-slate-900"><?php echo e($txn->type === 'payout' ? '₹ '.number_format((float) $txn->payout_charge, 2) : '—'); ?></td>
                                <td class="whitespace-nowrap px-4 py-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold <?php echo e($tone); ?>"><?php echo e(ucfirst((string) $txn->status)); ?></span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-xs text-slate-500"><?php echo e($txn->created_at?->timezone(config('app.timezone'))->format('d M, h:i A')); ?></td>
                                <td class="whitespace-nowrap px-4 py-2"><?php echo $__env->make('livewire.admin.partials.txn-actions', ['id' => $txn->id, 'reference' => $txn->reference, 'type' => $txn->type], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">No transactions yet.</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="fi-card min-w-0 overflow-hidden">
            <div class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-900">Needs attention</h2>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($alert['href']); ?>" class="flex items-center justify-between border-b border-slate-100 px-4 py-3 last:border-0 hover:bg-slate-50">
                    <span class="text-sm font-medium text-slate-700"><?php echo e($alert['label']); ?></span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold <?php echo e($alert['count'] > 0 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600'); ?>"><?php echo e(number_format($alert['count'])); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/admin/dashboard.blade.php ENDPATH**/ ?>