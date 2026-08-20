<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">API & Developer</h1>
        <p class="mt-1 text-sm text-slate-500">Manage your API credentials, webhook, and IP whitelist.</p>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($saved): ?>
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            ✓ Settings saved successfully.
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        
        <div class="space-y-5 lg:col-span-2">

            
            <div class="fi-card p-6">
                <h3 class="mb-5 text-sm font-semibold text-slate-900">API Credentials</h3>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$creds): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center">
                        <p class="text-sm text-slate-500">No API credentials generated yet.</p>
                        <button wire:click="regenerateApiKey"
                                class="mt-3 rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">
                            Generate API Credentials
                        </button>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        
                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="text-xs font-semibold text-slate-600">API Key (Public)</label>
                                <button wire:click="regenerateApiKey"
                                        wire:confirm="Regenerate API key? Old key will stop working."
                                        class="text-xs font-semibold text-violet-600 hover:underline">Regenerate</button>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="<?php echo e($creds->api_key); ?>"
                                       class="fi-input flex-1 bg-slate-50 font-mono text-xs text-slate-700"
                                       onclick="this.select()">
                                <button onclick="navigator.clipboard.writeText('<?php echo e($creds->api_key); ?>')"
                                        class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                    Copy
                                </button>
                            </div>
                        </div>

                        
                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="text-xs font-semibold text-slate-600">Secret Key (Private)</label>
                                <button wire:click="regenerateSecret"
                                        wire:confirm="Regenerate secret? This will invalidate your current secret immediately."
                                        class="text-xs font-semibold text-red-600 hover:underline">Regenerate</button>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="<?php echo e($showSecret ? 'text' : 'password'); ?>" readonly value="<?php echo e($creds->secret_key); ?>"
                                       class="fi-input flex-1 bg-slate-50 font-mono text-xs text-slate-700"
                                       onclick="this.select()">
                                <button wire:click="$toggle('showSecret')"
                                        class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                    <?php echo e($showSecret ? 'Hide' : 'Show'); ?>

                                </button>
                                <button onclick="navigator.clipboard.writeText('<?php echo e($creds->secret_key); ?>')"
                                        class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                    Copy
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">⚠ Never share your secret key. Use it only server-side.</p>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="fi-card p-6">
                <h3 class="mb-5 text-sm font-semibold text-slate-900">Webhook & IP Whitelist</h3>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Webhook URL</label>
                        <input wire:model="webhookUrl" type="url" class="fi-input text-sm"
                               placeholder="https://yourdomain.com/webhook/finpay">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['webhookUrl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <p class="mt-1 text-xs text-slate-400">We'll POST transaction updates to this URL automatically.</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">IP Whitelist</label>
                        <textarea wire:model="ipWhitelist" rows="4" class="fi-input font-mono text-sm"
                                  placeholder="One IP per line&#10;192.168.1.100&#10;10.0.0.0/24"></textarea>
                        <p class="mt-1 text-xs text-slate-400">Only these IPs can call our API. Leave empty to allow all.</p>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button wire:click="saveSettings"
                                class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                            Save Settings
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="fi-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-sm font-semibold text-slate-900">Recent Webhook Logs</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Last 10 webhook delivery attempts</p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logs->isEmpty()): ?>
                    <div class="flex items-center justify-center py-10 text-sm text-slate-400">No webhook logs yet.</div>
                <?php else: ?>
                    <div class="overflow-x-auto fi-scroll">
                        <table class="min-w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Event','Status','Response','Attempts','Date']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400"><?php echo e($col); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $lc = match($log->status) { 'success'=>'bg-emerald-50 text-emerald-700','failed'=>'bg-red-50 text-red-600',default=>'bg-amber-50 text-amber-700' }; ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-5 py-3 text-xs font-semibold text-slate-800"><?php echo e($log->event); ?></td>
                                        <td class="whitespace-nowrap px-5 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($lc); ?>"><?php echo e(ucfirst($log->status)); ?></span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500"><?php echo e($log->response_code ?? '—'); ?></td>
                                        <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500"><?php echo e($log->attempts); ?></td>
                                        <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-400"><?php echo e($log->created_at->format('d M, h:i A')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="space-y-5">
            <div class="fi-card p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">API Endpoints</h3>
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        ['POST', '/api/v1/auth/token',        'Get access token'],
                        ['GET',  '/api/v1/balance',           'Check balance'],
                        ['POST', '/api/v1/send-money',        'Initiate transfer'],
                        ['GET',  '/api/v1/transaction-status','Check status'],
                        ['POST', '/api/v1/beneficiaries',     'Add beneficiary'],
                        ['GET',  '/api/v1/reports',           'Transaction report'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$method,$ep,$desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                            <div class="flex items-center gap-2">
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-bold <?php echo e($method==='POST' ? 'bg-violet-100 text-violet-700' : 'bg-emerald-100 text-emerald-700'); ?>"><?php echo e($method); ?></span>
                                <code class="text-[11px] text-slate-600"><?php echo e($ep); ?></code>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-400"><?php echo e($desc); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="fi-card p-5">
                <h3 class="mb-3 text-sm font-semibold text-slate-900">Authentication</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Include your API key in the request header:</p>
                <div class="mt-2 rounded-lg bg-slate-900 p-3">
                    <code class="text-[11px] text-emerald-400">X-API-Key: <?php echo e($creds?->api_key ?? 'your_api_key'); ?></code><br>
                    <code class="text-[11px] text-slate-400">X-Signature: hmac_sha256</code>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/sandeep/Documents/finpay/resources/views/livewire/vendor/developer.blade.php ENDPATH**/ ?>