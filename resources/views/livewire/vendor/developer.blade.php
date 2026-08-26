<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">API documentation</h1>
        <p class="mt-1 text-sm text-slate-500">HMAC signing format and the FinPay APIs assigned to your account. Bank credentials are never shared with vendors.</p>
    </div>

    @if($saved)
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            Access settings saved.
        </div>
    @endif

    <div class="space-y-5">
        <div class="fi-card p-6">
            <h2 class="text-sm font-semibold text-slate-900">Authentication</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">Every request must include these headers. Sign with HMAC-SHA256 using your API secret. Do not send the secret in the request.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-2 pr-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Header</th>
                            <th class="py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr><td class="py-2 pr-4 font-mono text-xs">X-API-Key</td><td class="py-2 text-slate-600">Your public API key (from FinPay)</td></tr>
                        <tr><td class="py-2 pr-4 font-mono text-xs">X-Timestamp</td><td class="py-2 text-slate-600">UNIX epoch in seconds. Must be within 300 seconds.</td></tr>
                        <tr><td class="py-2 pr-4 font-mono text-xs">X-Nonce</td><td class="py-2 text-slate-600">Random string, unique per request, min 16 characters</td></tr>
                        <tr><td class="py-2 pr-4 font-mono text-xs">X-Signature</td><td class="py-2 text-slate-600">HMAC-SHA256 hex of the canonical string</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-xs font-semibold text-slate-700">Canonical string (five lines, newline separated)</p>
            <pre class="mt-2 max-h-40 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-emerald-300">timestamp
nonce
METHOD
/path?query
sha256(raw_body)</pre>
            <p class="mt-2 text-xs text-slate-500">Path must start with a leading slash, e.g. <code class="font-mono">/api/v1/bank/HDFC/payout</code>. For GET with no body, hash an empty string. Calls are allowed only from your IP whitelist.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="fi-card p-6">
                <h3 class="text-sm font-semibold text-slate-900">PHP</h3>
                <pre class="mt-3 max-h-64 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[11px] leading-5 text-slate-100">$timestamp = (string) time();
$nonce = bin2hex(random_bytes(16));
$method = 'POST';
$path = '/api/v1/bank/HDFC/payout';
$body = json_encode($payload, JSON_UNESCAPED_SLASHES);
$canonical = implode("\n", [
    $timestamp,
    $nonce,
    $method,
    $path,
    hash('sha256', $body),
]);
$signature = hash_hmac('sha256', $canonical, $apiSecret);

$ch = curl_init($apiBase.$path);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: '.$apiKey,
        'X-Timestamp: '.$timestamp,
        'X-Nonce: '.$nonce,
        'X-Signature: '.$signature,
    ],
    CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);</pre>
            </div>
            <div class="fi-card p-6">
                <h3 class="text-sm font-semibold text-slate-900">Node.js</h3>
                <pre class="mt-3 max-h-64 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[11px] leading-5 text-slate-100">const crypto = require('crypto');
const timestamp = String(Math.floor(Date.now() / 1000));
const nonce = crypto.randomBytes(16).toString('hex');
const method = 'POST';
const path = '/api/v1/bank/HDFC/payout';
const body = JSON.stringify(payload);
const canonical = [timestamp, nonce, method, path,
  crypto.createHash('sha256').update(body).digest('hex')].join('\n');
const signature = crypto.createHmac('sha256', apiSecret)
  .update(canonical).digest('hex');

await fetch(apiBase + path, {
  method,
  headers: {
    'Content-Type': 'application/json',
    'X-API-Key': apiKey,
    'X-Timestamp': timestamp,
    'X-Nonce': nonce,
    'X-Signature': signature,
  },
  body,
});</pre>
            </div>
        </div>

        <div class="fi-card p-6">
            <h2 class="text-sm font-semibold text-slate-900">Assigned APIs</h2>
            @forelse($assignedBanks as $bank)
                <div class="mt-5 border-t border-slate-100 pt-5 first:mt-3 first:border-t-0 first:pt-0">
                    <p class="text-base font-semibold text-slate-900">{{ $bank->name }}</p>
                    <p class="font-mono text-xs text-slate-500">{{ $bank->code }} · {{ strtoupper($bank->environment) }} · {{ strtoupper(implode(', ', $bank->services ?: ['imps','neft','rtgs'])) }}</p>

                    @forelse($bank->apiEndpoints as $ep)
                        <div class="mt-4 rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $ep->method === 'GET' ? 'bg-emerald-100 text-emerald-700' : 'bg-violet-100 text-violet-700' }}">{{ $ep->method }}</span>
                                <code class="break-all text-xs text-slate-800">{{ $apiBase }}/api/v1/bank/{{ $bank->code }}/{{ $ep->slug }}</code>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $ep->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $ep->description }}</p>

                            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Request parameters</p>
                                    @if(empty($ep->request_params))
                                        <p class="text-xs text-slate-500">No body parameters. Sign an empty body hash.</p>
                                    @else
                                        <table class="min-w-full text-xs">
                                            <thead>
                                                <tr class="border-b border-slate-100 text-left text-slate-400">
                                                    <th class="py-1 pr-2">Field</th>
                                                    <th class="py-1 pr-2">Type</th>
                                                    <th class="py-1 pr-2">Req</th>
                                                    <th class="py-1">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($ep->request_params as $p)
                                                    <tr class="border-b border-slate-50">
                                                        <td class="py-1.5 pr-2 font-mono">{{ $p['name'] ?? '' }}</td>
                                                        <td class="py-1.5 pr-2">{{ $p['type'] ?? 'string' }}</td>
                                                        <td class="py-1.5 pr-2">{{ !empty($p['required']) ? 'Yes' : 'No' }}</td>
                                                        <td class="py-1.5 text-slate-500">{{ $p['description'] ?? '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                    <p class="mb-1 mt-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Sample request</p>
                                    <pre class="overflow-x-auto rounded-lg bg-slate-50 p-3 font-mono text-[11px] text-slate-700">{{ json_encode($ep->sample_request ?: new stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                                <div>
                                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Response parameters</p>
                                    @if(empty($ep->response_params))
                                        <p class="text-xs text-slate-500">See sample response.</p>
                                    @else
                                        <table class="min-w-full text-xs">
                                            <thead>
                                                <tr class="border-b border-slate-100 text-left text-slate-400">
                                                    <th class="py-1 pr-2">Field</th>
                                                    <th class="py-1 pr-2">Type</th>
                                                    <th class="py-1">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($ep->response_params as $p)
                                                    <tr class="border-b border-slate-50">
                                                        <td class="py-1.5 pr-2 font-mono">{{ $p['name'] ?? '' }}</td>
                                                        <td class="py-1.5 pr-2">{{ $p['type'] ?? 'string' }}</td>
                                                        <td class="py-1.5 text-slate-500">{{ $p['description'] ?? '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                    <p class="mb-1 mt-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Sample response</p>
                                    <pre class="overflow-x-auto rounded-lg bg-slate-50 p-3 font-mono text-[11px] text-slate-700">{{ json_encode($ep->sample_response ?: new stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="mt-3 text-sm text-slate-500">Admin has not mapped bank APIs yet.</p>
                    @endforelse
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-500">Admin has not assigned a bank to your account yet. APIs will appear here after assignment.</p>
            @endforelse
        </div>

        <div class="fi-card p-6">
            <h2 class="text-sm font-semibold text-slate-900">Access settings</h2>
            <p class="mt-1 text-xs text-slate-500">IP whitelist is required. Credentials are not shown on this page.</p>
            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Webhook URL</label>
                    <input wire:model="webhookUrl" type="url" class="fi-input text-sm" placeholder="https://yourdomain.com/webhook/finpay">
                    @error('webhookUrl')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">IP whitelist *</label>
                    <textarea wire:model="ipWhitelist" rows="4" class="fi-input font-mono text-sm" placeholder="203.0.113.10"></textarea>
                    @error('ipWhitelist')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button wire:click="saveSettings" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">Save settings</button>
            </div>
        </div>
    </div>
</div>
