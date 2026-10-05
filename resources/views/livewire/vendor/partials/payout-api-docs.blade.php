@php
    $payoutProviderName = $payoutProviderName ?? '';
    $payoutEnvironment = $payoutEnvironment ?? '';
    $exampleBody = '{"merchantRefId":"MERCHANT-REF-1","amount":100,"beneficiaryBank":"001","paymentPurpose":"004","paymentMode":"IMPS","beneficiaryAccountNumber":"12345678901234","beneficiaryIFSC":"HDFC0001234","beneficiaryMobileNumber":"9999999999","beneficiaryName":"Test Beneficiary","beneficiaryLocation":"AS","lat":"26.1","long":"91.7"}';
@endphp

<div class="space-y-5">
    <div class="rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-950">
        <p class="font-bold">Do not send the API secret in request headers.</p>
        <p class="mt-1">Do not expose the API secret in a frontend, browser, or mobile application.</p>
        <p class="mt-1">Use HTTPS.</p>
        <p class="mt-1">Generate a new nonce for every request.</p>
    </div>

    <section class="fi-card p-6">
        <p class="text-sm font-semibold text-slate-900">Payout Provider: {{ $payoutProviderName }}</p>
    </section>

    <section class="fi-card p-6">
        <h2 class="text-lg font-bold text-slate-900">Credentials issued by FinPe</h2>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">These values belong to your vendor account. FinPe issues them. This page does not print the API key or the API secret. The secret is shown only once, when FinPe generates or rotates it, and is not shown again.</p>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-2 pr-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Item</th>
                        <th class="py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">What FinPe provides</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr>
                        <td class="py-2 pr-4 font-semibold text-slate-900">API Key / Client Key</td>
                        <td class="py-2 text-slate-600">Sent as <span class="font-mono text-xs">X-API-Key</span>. It identifies your credential. It is not displayed here.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-semibold text-slate-900">API Secret</td>
                        <td class="py-2 text-slate-600">Used only to calculate <span class="font-mono text-xs">X-Signature</span>. Never send it as a header or body field.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-semibold text-slate-900">API Base URL</td>
                        <td class="py-2 font-mono text-xs text-slate-800">{{ $apiBase }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-semibold text-slate-900">Whitelisted server IP</td>
                        <td class="py-2 text-slate-600">Your server’s public IP must be on that credential’s IP whitelist. An empty whitelist is denied. No sample IP is listed here.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-semibold text-slate-900">Environment</td>
                        <td class="py-2 text-slate-600">Payout provider environment from the current FinPe configuration: <span class="font-semibold">{{ $payoutEnvironment !== '' ? $payoutEnvironment : '—' }}</span>. The base URL above is the current application API base.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-2xl border border-violet-200 bg-violet-50 p-6">
        <h2 class="text-lg font-bold text-slate-900">What FinPe gives you</h2>
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-violet-800">FinPe provides</p>
                <ol class="mt-2 list-decimal space-y-1 pl-5 text-sm text-slate-800">
                    <li>API Base URL</li>
                    <li>API Key</li>
                    <li>API Secret</li>
                    <li>IP whitelist configuration</li>
                    <li>UAT/Production environment details (current value: {{ $payoutEnvironment !== '' ? $payoutEnvironment : '—' }})</li>
                </ol>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-violet-800">You generate for each request</p>
                <ol class="mt-2 list-decimal space-y-1 pl-5 text-sm text-slate-800">
                    <li>Timestamp</li>
                    <li>Nonce</li>
                    <li>HMAC signature</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="fi-card p-6">
        <h2 class="text-lg font-bold text-slate-900">Authentication</h2>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">There is no separate authentication, login, token, or bearer API for the Payout API. Every vendor payout request is authenticated with these four headers:</p>
        <ul class="mt-3 list-disc space-y-1 pl-5 font-mono text-sm text-slate-800">
            <li>X-API-Key</li>
            <li>X-Timestamp</li>
            <li>X-Nonce</li>
            <li>X-Signature</li>
        </ul>
        <p class="mt-3 text-sm text-slate-600">The provider callback is separate. <span class="font-mono">POST {{ $apiBase }}/v1/payout/callback</span> is not behind this HMAC middleware and does not use these headers.</p>
    </section>

    <section class="fi-card p-6" id="hmac">
        <h2 class="text-lg font-bold text-slate-900">HMAC-SHA256</h2>
        <p class="mt-2 text-sm text-slate-600">This is the signing rule implemented by FinPe. Read the API secret from your server environment. Do not hardcode it.</p>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <tbody class="divide-y divide-slate-50">
                    <tr><td class="py-2 pr-4 font-semibold text-slate-900">Algorithm</td><td class="py-2 text-slate-600">HMAC-SHA256</td></tr>
                    <tr><td class="py-2 pr-4 font-semibold text-slate-900">Output</td><td class="py-2 text-slate-600">Lowercase hexadecimal. PHP <span class="font-mono text-xs">hash_hmac('sha256', canonical, secret)</span> returns this encoding.</td></tr>
                    <tr><td class="py-2 pr-4 font-semibold text-slate-900">Formula</td><td class="py-2 font-mono text-xs text-slate-800">hash_hmac('sha256', canonicalString, apiSecret)</td></tr>
                    <tr><td class="py-2 pr-4 font-semibold text-slate-900">Newlines</td><td class="py-2 text-slate-600">Five lines joined with LF (<span class="font-mono text-xs">\n</span>). There is no trailing newline after the fifth line.</td></tr>
                </tbody>
            </table>
        </div>

        <h3 class="mt-5 text-sm font-semibold text-slate-900">Canonical string, five lines separated by newlines</h3>
        <div class="mt-2 flex items-center justify-end">
            <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50" onclick="finpeCopy('hmac-canonical', this)">Copy</button>
        </div>
        <pre id="hmac-canonical" class="overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-emerald-300">timestamp
nonce
METHOD
/path
hash("sha256", raw_body)</pre>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-2 pr-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Line</th>
                        <th class="py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Exact value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr><td class="py-2 pr-4 font-mono text-xs">1</td><td class="py-2 text-slate-600"><span class="font-mono text-xs">X-Timestamp</span>. UNIX epoch seconds, digits only. Must be within 300 seconds of FinPe’s server time.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">2</td><td class="py-2 text-slate-600"><span class="font-mono text-xs">X-Nonce</span>. 16 to 64 characters matching <span class="font-mono text-xs">^[A-Za-z0-9._-]{16,64}$</span>. A nonce already used for this credential inside 300 seconds is rejected.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">3</td><td class="py-2 text-slate-600">HTTP method in uppercase. FinPe applies <span class="font-mono text-xs">strtoupper</span>, so this line is <span class="font-mono text-xs">GET</span> or <span class="font-mono text-xs">POST</span>.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">4</td><td class="py-2 text-slate-600">A leading slash plus the request path. For these routes that path is <span class="font-mono text-xs">/api/v1/...</span>. If the request has a query string, FinPe appends <span class="font-mono text-xs">?</span> and the raw query string.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">5</td><td class="py-2 text-slate-600">Lowercase hex SHA-256 of the raw request body. The implementation is <span class="font-mono text-xs">hash('sha256', raw body)</span>. An empty body hashes an empty string, which is <span class="font-mono text-xs">e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</span>.</td></tr>
                </tbody>
            </table>
        </div>

        <h3 class="mt-6 text-sm font-semibold text-slate-900">Required headers</h3>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-2 pr-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Header</th>
                        <th class="py-2 pr-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Required</th>
                        <th class="py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr><td class="py-2 pr-4 font-mono text-xs">X-API-Key</td><td class="py-2 pr-4"><span class="rounded bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Required</span></td><td class="py-2 text-slate-600">API key issued by FinPe for this vendor.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">X-Timestamp</td><td class="py-2 pr-4"><span class="rounded bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Required</span></td><td class="py-2 text-slate-600">UNIX epoch seconds. You generate this when sending the request.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">X-Nonce</td><td class="py-2 pr-4"><span class="rounded bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Required</span></td><td class="py-2 text-slate-600">Unique value you generate for this request.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">X-Signature</td><td class="py-2 pr-4"><span class="rounded bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Required</span></td><td class="py-2 text-slate-600">HMAC-SHA256 hex of the canonical string, using your API secret.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">Content-Type</td><td class="py-2 pr-4"><span class="rounded bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Required for JSON</span></td><td class="py-2 text-slate-600"><span class="font-mono text-xs">application/json</span> on POST so the JSON body is read. It is not part of the canonical string. Sign the same bytes you send.</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-sm text-slate-600">Sending <span class="font-mono text-xs">X-API-Secret</span>, or a body field <span class="font-mono text-xs">secret_key</span> or <span class="font-mono text-xs">api_secret</span>, returns HTTP 400 <span class="font-mono text-xs">{"success":false,"message":"Do not send the API secret. Sign the request with HMAC-SHA256 instead."}</span></p>

        <h3 class="mt-6 text-sm font-semibold text-slate-900">Step-by-step</h3>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-700">
            <li><span class="font-semibold text-slate-900">Generate a UNIX timestamp.</span> Seconds since the epoch, digits only. Example placeholder: <span class="font-mono text-xs">1710000000</span>.</li>
            <li><span class="font-semibold text-slate-900">Generate a unique nonce.</span> 16 to 64 characters from letters, digits, <span class="font-mono text-xs">.</span>, <span class="font-mono text-xs">_</span>, or <span class="font-mono text-xs">-</span>. Do not reuse it for this credential within 300 seconds.</li>
            <li><span class="font-semibold text-slate-900">Prepare the exact request body.</span> Build the JSON string first. For GET, the body is empty. The fifth line hashes those exact bytes.</li>
            <li><span class="font-semibold text-slate-900">Build the canonical string</span> in this order, joined by LF and without a trailing newline: timestamp, nonce, uppercase method, path, SHA-256 hex of the raw body.</li>
            <li><span class="font-semibold text-slate-900">Calculate HMAC-SHA256</span> with your API secret as the key and the canonical string as the message.</li>
            <li><span class="font-semibold text-slate-900">Use lowercase hex.</span> That is the encoding <span class="font-mono text-xs">hash_hmac</span> returns. FinPe also lowercases the header before comparing it.</li>
            <li><span class="font-semibold text-slate-900">Send X-Signature</span> with <span class="font-mono text-xs">X-API-Key</span>, <span class="font-mono text-xs">X-Timestamp</span>, and <span class="font-mono text-xs">X-Nonce</span>. Do not send the API secret.</li>
        </ol>

        <h3 class="mt-6 text-sm font-semibold text-slate-900">Example headers</h3>
        <p class="mt-1 text-xs text-slate-500">Placeholders only. Replace the key, timestamp, nonce, and signature. The signature below is not a real signature.</p>
        <div class="mt-2 flex justify-end">
            <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50" onclick="finpeCopy('hmac-headers', this)">Copy</button>
        </div>
        <pre id="hmac-headers" class="mt-2 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">Content-Type: application/json
X-API-Key: YOUR_API_KEY
X-Timestamp: 1710000000
X-Nonce: a1b2c3d4e5f67890
X-Signature: YOUR_LOWERCASE_HEX_SIGNATURE</pre>
    </section>

    <section class="fi-card p-6">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-md bg-violet-600 px-2 py-1 font-mono text-xs font-bold text-white">POST</span>
            <h2 class="text-lg font-bold text-slate-900">Create payout</h2>
        </div>
        <p class="mt-2 font-mono text-sm text-slate-800">{{ $apiBase }}/v1/payouts</p>
        <p class="mt-2 text-sm text-slate-600">Authenticated with the HMAC headers above. One payout for the authenticated vendor.</p>

        <h3 class="mt-5 text-sm font-semibold text-slate-900">Parameters</h3>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-2 pr-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Field</th>
                        <th class="py-2 pr-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Type</th>
                        <th class="py-2 pr-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500"></th>
                        <th class="py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Rule</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach([
                        ['merchantRefId', 'string', 'Required', 'Max 40. Unique for this vendor. A repeat returns HTTP 409.'],
                        ['amount', 'numeric', 'Required', 'At least 100 and below 100000, and within the vendor transaction limit when one is set.'],
                        ['beneficiaryBank', 'string', 'Required', 'Max 10. Use a code from GET /api/v1/payout/banks.'],
                        ['paymentPurpose', 'string', 'Required', 'Max 20. Use a code from GET /api/v1/payout/purposes.'],
                        ['paymentMode', 'string', 'Required', 'Max 10. IMPS or NEFT.'],
                        ['beneficiaryAccountNumber', 'string', 'Required', 'Max 32. The create response masks it.'],
                        ['beneficiaryIFSC', 'string', 'Required', 'Max 20.'],
                        ['beneficiaryMobileNumber', 'string', 'Required', 'Max 15.'],
                        ['beneficiaryName', 'string', 'Required', 'Max 120.'],
                        ['beneficiaryLocation', 'string', 'Required', 'Max 20. Use a code from GET /api/v1/payout/states.'],
                        ['lat', 'string', 'Required', 'Max 30.'],
                        ['long', 'string', 'Required', 'Max 30.'],
                        ['udf1', 'string', 'Optional', 'Max 100.'],
                        ['udf2', 'string', 'Optional', 'Max 100.'],
                        ['udf3', 'string', 'Optional', 'Max 100.'],
                    ] as [$field, $type, $need, $rule])
                        <tr>
                            <td class="py-2 pr-3 font-mono text-xs">{{ $field }}</td>
                            <td class="py-2 pr-3 text-slate-700">{{ $type }}</td>
                            <td class="py-2 pr-3">
                                <span class="rounded px-2 py-0.5 text-xs font-bold {{ $need === 'Required' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600' }}">{{ $need }}</span>
                            </td>
                            <td class="py-2 text-slate-600">{{ $rule }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-xs text-slate-500">Example bank, purpose, and state codes below are placeholders. Take the live codes from the master list endpoints.</p>

        <div class="mt-4 flex justify-end">
            <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50" onclick="finpeCopy('payout-body', this)">Copy</button>
        </div>
        <pre id="payout-body" class="mt-2 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">{{ $exampleBody }}</pre>

        <h3 class="mt-5 text-sm font-semibold text-slate-900">Success response, HTTP 201</h3>
        <p class="mt-1 text-sm text-slate-600">HTTP 201 means the payout was stored. Read <span class="font-mono text-xs">data.status</span>. For success, <span class="font-mono text-xs">message</span> is “Payout successful.” <span class="font-mono text-xs">charge</span> and <span class="font-mono text-xs">total_debited</span> are the stored payout charge and amount plus charge. The numbers below are an example shape, not a fixed charge.</p>
        <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">{
  "success": true,
  "message": "Payout successful.",
  "data": {
    "reference": "TXN-EXAMPLE",
    "merchant_ref": "MERCHANT-REF-1",
    "amount": 100,
    "charge": 0,
    "total_debited": 100,
    "payment_mode": "IMPS",
    "beneficiary_bank": "001",
    "account_number": "XXXXXXXXXX1234",
    "ifsc": "HDFC0001234",
    "provider_reference": null,
    "status": "success",
    "created_at": "2026-10-05T06:00:00+05:30"
  }
}</pre>

        <h3 class="mt-5 text-sm font-semibold text-slate-900">Pending response, HTTP 201</h3>
        <p class="mt-1 text-sm text-slate-600">Same body. <span class="font-mono text-xs">message</span> is “Payout initiated.” and <span class="font-mono text-xs">data.status</span> is “pending”.</p>
        <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">{
  "success": true,
  "message": "Payout initiated.",
  "data": {
    "reference": "TXN-EXAMPLE",
    "merchant_ref": "MERCHANT-REF-1",
    "amount": 100,
    "charge": 0,
    "total_debited": 100,
    "payment_mode": "IMPS",
    "beneficiary_bank": "001",
    "account_number": "XXXXXXXXXX1234",
    "ifsc": "HDFC0001234",
    "provider_reference": null,
    "status": "pending",
    "created_at": "2026-10-05T06:00:00+05:30"
  }
}</pre>

        <h3 class="mt-5 text-sm font-semibold text-slate-900">Failed payout that was still stored, HTTP 201</h3>
        <p class="mt-1 text-sm text-slate-600"><span class="font-mono text-xs">data.status</span> is “failed”. <span class="font-mono text-xs">message</span> is the stored failure reason, or “Payout failed.” when that reason is empty.</p>
        <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">{
  "success": true,
  "message": "Payout failed.",
  "data": {
    "reference": "TXN-EXAMPLE",
    "merchant_ref": "MERCHANT-REF-1",
    "amount": 100,
    "charge": 0,
    "total_debited": 100,
    "payment_mode": "IMPS",
    "beneficiary_bank": "001",
    "account_number": "XXXXXXXXXX1234",
    "ifsc": "HDFC0001234",
    "provider_reference": null,
    "status": "failed",
    "created_at": "2026-10-05T06:00:00+05:30"
  }
}</pre>

        <h3 class="mt-5 text-sm font-semibold text-slate-900">Validation failed, HTTP 422</h3>
        <p class="mt-1 text-sm text-slate-600">A missing or invalid JSON field returns Laravel’s validation body: <span class="font-mono text-xs">message</span> and <span class="font-mono text-xs">errors</span> keyed by field name. A payout rule that rejects the request before or instead of that uses <span class="font-mono text-xs">{"success":false,"message":"..."}</span>. Exact rule messages include “Amount must be at least 100 and below 100000.”, “Invalid payment mode. Use IMPS or NEFT.”, “This reference was already used.” (HTTP 409), and “API access is disabled for this vendor. Contact admin.” (HTTP 403).</p>
    </section>

    <section class="fi-card p-6">
        <h2 class="text-lg font-bold text-slate-900">Master APIs</h2>
        <p class="mt-2 text-sm text-slate-600">Each call uses the same HMAC headers. There is no request body, so the fifth canonical line is the SHA-256 of an empty string. A successful body is <span class="font-mono text-xs">success</span>, <span class="font-mono text-xs">message</span>, and <span class="font-mono text-xs">data</span> as a list of <span class="font-mono text-xs">code</span> and <span class="font-mono text-xs">description</span>.</p>
        @foreach([
            ['GET', '/v1/payout/banks', '/api/v1/payout/banks', 'Bank codes for beneficiaryBank.'],
            ['GET', '/v1/payout/purposes', '/api/v1/payout/purposes', 'Purpose codes for paymentPurpose.'],
            ['GET', '/v1/payout/states', '/api/v1/payout/states', 'State codes for beneficiaryLocation.'],
        ] as [$method, $urlPath, $signPath, $purpose])
            <div class="mt-4 rounded-xl border border-slate-200 p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-emerald-600 px-2 py-1 font-mono text-xs font-bold text-white">{{ $method }}</span>
                    <code class="text-sm font-semibold text-slate-900">{{ $apiBase }}{{ $urlPath }}</code>
                </div>
                <p class="mt-2 text-sm text-slate-600">{{ $purpose }} Signed path: <span class="font-mono text-xs">{{ $signPath }}</span></p>
            </div>
        @endforeach
        <pre class="mt-4 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">{
  "success": true,
  "message": "",
  "data": [
    {"code": "", "description": ""}
  ]
}</pre>
        <p class="mt-2 text-xs text-slate-500"><span class="font-mono">message</span> is the text returned for that master call. <span class="font-mono">data</span> is the list of <span class="font-mono">code</span> and <span class="font-mono">description</span>. The values above are empty placeholders, not a live list.</p>
    </section>

    <section class="fi-card p-6">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-md bg-emerald-600 px-2 py-1 font-mono text-xs font-bold text-white">GET</span>
            <h2 class="text-lg font-bold text-slate-900">Transaction status</h2>
        </div>
        <p class="mt-2 font-mono text-sm text-slate-800">{{ $apiBase }}/v1/payouts/{reference}</p>
        <p class="mt-2 text-sm text-slate-600"><span class="font-mono text-xs">reference</span> is the FinPe reference returned as <span class="font-mono text-xs">data.reference</span> on create, for example <span class="font-mono text-xs">{{ $apiBase }}/v1/payouts/TXN-EXAMPLE</span>. The signed path is <span class="font-mono text-xs">/api/v1/payouts/TXN-EXAMPLE</span>. The lookup is limited to the authenticated vendor. Use the same HMAC headers and an empty body hash.</p>
        <p class="mt-2 text-sm text-slate-600">HTTP 200 returns <span class="font-mono text-xs">{"success":true,"data":{...}}</span>. <span class="font-mono text-xs">data</span> contains <span class="font-mono text-xs">reference</span>, <span class="font-mono text-xs">bank_code</span>, <span class="font-mono text-xs">bank_reference</span>, <span class="font-mono text-xs">amount</span>, <span class="font-mono text-xs">charge</span>, <span class="font-mono text-xs">total_debited</span>, <span class="font-mono text-xs">service</span>, <span class="font-mono text-xs">status</span>, <span class="font-mono text-xs">beneficiary_name</span>, <span class="font-mono text-xs">account_number</span>, <span class="font-mono text-xs">ifsc_code</span>, <span class="font-mono text-xs">failure_reason</span>, and <span class="font-mono text-xs">created_at</span>. <span class="font-mono text-xs">status</span> is the stored status: success, failed, or pending. A missing reference returns HTTP 404 <span class="font-mono text-xs">{"success":false,"message":"Transaction not found."}</span></p>
        <p class="mt-2 text-sm text-slate-600"><span class="font-mono text-xs">GET {{ $apiBase }}/v1/payouts</span> returns the latest 50 transactions for this vendor as <span class="font-mono text-xs">{"success":true,"data":[...]}</span> using the same item fields. Signed path: <span class="font-mono text-xs">/api/v1/payouts</span>.</p>
    </section>

    <section class="fi-card p-6">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-md bg-violet-600 px-2 py-1 font-mono text-xs font-bold text-white">POST</span>
            <h2 class="text-lg font-bold text-slate-900">Callback</h2>
        </div>
        <p class="mt-2 font-mono text-sm text-slate-800">{{ $apiBase }}/v1/payout/callback</p>
        <p class="mt-2 text-sm text-slate-600">This route is not signed with the vendor HMAC headers. FinPe does not require <span class="font-mono text-xs">X-API-Key</span> or <span class="font-mono text-xs">X-Signature</span> on it. It is limited to 60 requests per minute.</p>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="border-b border-slate-100"><th class="py-2 pr-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Field</th><th class="py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Rule</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach([
                        ['beneficiaryMobileNumber', 'Required string, max 15'],
                        ['beneficiaryBank', 'Required string, max 10'],
                        ['beneficiaryAccountNumber', 'Required string, max 32'],
                        ['beneficiaryIFSC', 'Required string, max 20'],
                        ['beneficiaryLocation', 'Required string, max 20'],
                        ['beneficiaryName', 'Required string, max 120'],
                        ['merchantRefId', 'Required string, max 40'],
                        ['amount', 'Required numeric, below 100000'],
                        ['charges', 'Required numeric'],
                        ['paymentMode', 'Required: IMPS, NEFT, imps, or neft'],
                        ['paymentPurpose', 'Required string, max 20'],
                        ['lat', 'Required'],
                        ['long', 'Required'],
                        ['udf1, udf2, udf3', 'Optional string, max 100'],
                        ['txnStatus', 'Required string, max 40'],
                        ['txnStatusCode', 'Required string, exactly 3 characters'],
                        ['txnId', 'Optional string, max 80'],
                        ['rrn', 'Optional string, max 40. Empty is rejected when txnStatusCode is 000 and txnStatus is success.'],
                        ['responseMessage', 'Required string, max 255'],
                    ] as [$field, $rule])
                        <tr><td class="py-2 pr-4 font-mono text-xs">{{ $field }}</td><td class="py-2 text-slate-600">{{ $rule }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <h3 class="mt-5 text-sm font-semibold text-slate-900">Status mapping</h3>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="border-b border-slate-100"><th class="py-2 pr-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">txnStatusCode</th><th class="py-2 pr-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">txnStatus</th><th class="py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Stored status</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    <tr><td class="py-2 pr-4 font-mono">000</td><td class="py-2">success</td><td class="py-2">success</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">001</td><td class="py-2">failed</td><td class="py-2">failed</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">002</td><td class="py-2">pending, inprogress, or in progress</td><td class="py-2">pending</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">003</td><td class="py-2">failed or validation failed</td><td class="py-2">failed</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">004</td><td class="py-2">queued or pending</td><td class="py-2">pending</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-sm text-slate-600">Any other combination returns HTTP 422 <span class="font-mono text-xs">{"successStatus":false,"message":"Invalid callback."}</span>. An accepted callback returns:</p>
        <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">{
  "successStatus": true,
  "message": "Success",
  "responseCode": "000"
}</pre>
        <p class="mt-3 text-sm text-slate-600">FinPe matches the existing payout by <span class="font-mono text-xs">txnId</span> when that provider reference is present, otherwise by a single <span class="font-mono text-xs">merchantRefId</span>. It does not create a second transaction. A repeat of an already applied status is accepted again and does not add another commission entry or another wallet movement. A failed status refunds the wallet only when the stored payout was not already failed. If the stored status is neither pending nor already equal to the callback status, the stored status is left unchanged.</p>
    </section>

    <section class="fi-card p-6">
        <h2 class="text-lg font-bold text-slate-900">Complete request example</h2>
        <p class="mt-2 text-sm text-slate-600">POST <span class="font-mono text-xs">{{ $apiBase }}/v1/payouts</span>. Sign path <span class="font-mono text-xs">/api/v1/payouts</span>. Hash the JSON string below exactly, then send that same string as the body.</p>
        <pre class="mt-3 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-emerald-300">timestamp
nonce
POST
/api/v1/payouts
hash("sha256", raw JSON body)</pre>
        <pre class="mt-3 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">{{ $exampleBody }}</pre>
    </section>

    <section class="fi-card p-6">
        <h2 class="text-lg font-bold text-slate-900">cURL</h2>
        <div class="mt-2 flex justify-end">
            <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50" onclick="finpeCopy('ex-curl', this)">Copy</button>
        </div>
        <pre id="ex-curl" class="mt-2 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">API_KEY="$FINPE_API_KEY"
API_SECRET="$FINPE_API_SECRET"
API_BASE="${FINPE_API_BASE%/}"
BODY='{{ $exampleBody }}'
TIMESTAMP="$(date +%s)"
NONCE="$(openssl rand -hex 16)"
METHOD="POST"
PATH="/api/v1/payouts"
BODY_HASH="$(printf '%s' "$BODY" | openssl dgst -sha256 | awk '{print $NF}')"
CANONICAL="$(printf '%s\n%s\n%s\n%s\n%s' "$TIMESTAMP" "$NONCE" "$METHOD" "$PATH" "$BODY_HASH")"
SIGNATURE="$(printf '%s' "$CANONICAL" | openssl dgst -sha256 -hmac "$API_SECRET" | awk '{print $NF}')"

curl -sS -X POST "$API_BASE/v1/payouts" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: $API_KEY" \
  -H "X-Timestamp: $TIMESTAMP" \
  -H "X-Nonce: $NONCE" \
  -H "X-Signature: $SIGNATURE" \
  --data-binary "$BODY"</pre>
        <p class="mt-2 text-xs text-slate-500"><span class="font-mono">FINPE_API_BASE</span> is the base URL shown above, ending in <span class="font-mono">/api</span>. The secret is read from the environment.</p>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="fi-card p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-slate-900">PHP</h2>
                <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50" onclick="finpeCopy('ex-php', this)">Copy</button>
            </div>
            <pre id="ex-php" class="mt-3 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">$apiKey = getenv('FINPE_API_KEY');
$apiSecret = getenv('FINPE_API_SECRET');
$apiBase = rtrim(getenv('FINPE_API_BASE'), '/');
$body = '{{ $exampleBody }}';
$timestamp = (string) time();
$nonce = bin2hex(random_bytes(16));
$method = 'POST';
$path = '/api/v1/payouts';
$canonical = implode("\n", [
    $timestamp,
    $nonce,
    $method,
    $path,
    hash('sha256', $body),
]);
$signature = hash_hmac('sha256', $canonical, $apiSecret);

$ch = curl_init($apiBase.'/v1/payouts');
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
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-slate-900">Node.js</h2>
                <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50" onclick="finpeCopy('ex-node', this)">Copy</button>
            </div>
            <pre id="ex-node" class="mt-3 overflow-x-auto rounded-lg bg-slate-900 p-3 text-[12px] leading-5 text-slate-100">const crypto = require('crypto');
const apiKey = process.env.FINPE_API_KEY;
const apiSecret = process.env.FINPE_API_SECRET;
const apiBase = process.env.FINPE_API_BASE.replace(/\/$/, '');
const body = '{{ $exampleBody }}';
const timestamp = String(Math.floor(Date.now() / 1000));
const nonce = crypto.randomBytes(16).toString('hex');
const method = 'POST';
const path = '/api/v1/payouts';
const bodyHash = crypto.createHash('sha256').update(body).digest('hex');
const canonical = [timestamp, nonce, method, path, bodyHash].join('\n');
const signature = crypto.createHmac('sha256', apiSecret).update(canonical).digest('hex');

await fetch(apiBase + '/v1/payouts', {
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
    </section>

    <section class="fi-card p-6">
        <h2 class="text-lg font-bold text-slate-900">Security</h2>
        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-700">
            <li>Do not send the API secret in a header, query string, or JSON field. Do not put it in a browser or mobile app.</li>
            <li>Use HTTPS. Responses that are already on HTTPS include <span class="font-mono text-xs">Strict-Transport-Security</span>.</li>
            <li>The API key and API secret are issued for one vendor. A request can read only that vendor’s payouts.</li>
            <li>The timestamp limits a signed request to 300 seconds. The nonce stops the same signed request from being replayed for that credential inside that window.</li>
            <li>The HMAC signature proves the request was signed with the API secret. FinPe compares it in lowercase hex.</li>
            <li>Do not share the API key or API secret.</li>
            <li>The caller IP must match the credential whitelist. An empty whitelist is denied with HTTP 403 <span class="font-mono text-xs">{"success":false,"message":"API access denied."}</span>. An entry may be one IP or a CIDR range accepted by the existing whitelist check. Save the whitelist from the existing API access settings. This page does not list an IP.</li>
            <li>Vendor API calls are limited to 60 per minute, keyed by <span class="font-mono text-xs">X-API-Key</span>, or by IP when that header is empty.</li>
            <li>After 20 failed authentication attempts from an IP, FinPe returns HTTP 429 <span class="font-mono text-xs">{"success":false,"message":"Too many failed authentication attempts. Try again later."}</span></li>
            <li>A raw body larger than 65536 bytes returns HTTP 413 <span class="font-mono text-xs">{"success":false,"message":"Request body is too large."}</span></li>
        </ul>
    </section>

    <section class="fi-card p-6">
        <h2 class="text-lg font-bold text-slate-900">HTTP status codes</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="border-b border-slate-100"><th class="py-2 pr-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">HTTP</th><th class="py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">When</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    <tr><td class="py-2 pr-4 font-mono">201</td><td class="py-2 text-slate-600">Payout stored. <span class="font-mono text-xs">data.status</span> may be success, pending, or failed.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">200</td><td class="py-2 text-slate-600">List, status lookup, or master list succeeded.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">400</td><td class="py-2 text-slate-600">API secret was sent in the request.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">401</td><td class="py-2 text-slate-600"><span class="font-mono text-xs">{"success":false,"message":"Authentication failed."}</span></td></tr>
                    <tr><td class="py-2 pr-4 font-mono">403</td><td class="py-2 text-slate-600"><span class="font-mono text-xs">{"success":false,"message":"API access denied."}</span> This includes a disabled payout API, an inactive vendor, or an IP that is not whitelisted.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">404</td><td class="py-2 text-slate-600">Payout reference was not found for this vendor.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">409</td><td class="py-2 text-slate-600"><span class="font-mono text-xs">{"success":false,"message":"This reference was already used."}</span></td></tr>
                    <tr><td class="py-2 pr-4 font-mono">413</td><td class="py-2 text-slate-600">Request body is too large.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">422</td><td class="py-2 text-slate-600">JSON validation failed, or a payout rule rejected the request.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">429</td><td class="py-2 text-slate-600">Failed authentication is limited to 20 attempts per IP, then <span class="font-mono text-xs">{"success":false,"message":"Too many failed authentication attempts. Try again later."}</span> Vendor API calls are also limited to 60 per minute.</td></tr>
                    <tr><td class="py-2 pr-4 font-mono">502</td><td class="py-2 text-slate-600"><span class="font-mono text-xs">{"success":false,"message":"Payout provider request failed."}</span></td></tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
<script>
function finpeCopy(id, button) {
    var node = document.getElementById(id);
    if (!node || !navigator.clipboard) return;
    navigator.clipboard.writeText(node.innerText).then(function () {
        var previous = button.textContent;
        button.textContent = 'Copied';
        setTimeout(function () { button.textContent = previous; }, 1500);
    });
}
</script>
