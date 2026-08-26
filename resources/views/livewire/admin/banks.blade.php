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

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
        @foreach([
            ['1', 'Add bank', 'Name, code, sandbox/live'],
            ['2', 'Integrate bank APIs', 'Map the 10–12 APIs the bank gave you'],
            ['3', 'Assign vendors', 'Vendor sees FinPay docs for those APIs'],
        ] as [$n, $t, $d])
            <div class="fi-card p-4">
                <p class="text-xs font-bold text-blue-700">Step {{ $n }}</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $t }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $d }}</p>
            </div>
        @endforeach
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <input wire:model.live.debounce.300ms="search" type="text" class="fi-input w-72 text-sm" placeholder="Search bank name or code...">
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @foreach(['Bank','Environment','Bank APIs','Vendors','Status','Last test','Action'] as $col)
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($banks as $bank)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $bank->name }}</p>
                                <p class="font-mono text-xs text-slate-500">{{ $bank->code }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-semibold text-slate-700">
                                {{ strtoupper($bank->environment) }} · {{ $bank->driver }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600">
                                <p class="font-semibold text-slate-800">{{ $bank->api_endpoints_count }} APIs</p>
                                <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ $bank->base_url ? $bank->base_url : 'Simulator / no URL' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-900">{{ $bank->vendors_count }}</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $bank->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $bank->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($bank->is_default)
                                    <span class="ml-1 text-[10px] font-semibold text-blue-700">Default</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $bank->last_test_status ? ucfirst($bank->last_test_status) : '—' }}
                                <div class="text-[11px] text-slate-400">{{ $bank->last_tested_at?->diffForHumans() }}</div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="openApis({{ $bank->id }})" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">Bank APIs</button>
                                    <button type="button" wire:click="openEdit({{ $bank->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Credentials</button>
                                    <button type="button" wire:click="openAssign({{ $bank->id }})" class="rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800">Assign vendors</button>
                                    <button type="button" wire:click="testConnection({{ $bank->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Test</button>
                                    <button type="button" wire:click="toggleActive({{ $bank->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        {{ $bank->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-sm text-slate-500">No banks yet. Add the first bank to start API integration.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $banks->links() }}</div>
    </div>

    @if($showModal)
        <div class="fi-modal-overlay">
            <div class="fi-modal fi-modal-lg">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $editingId ? 'Bank API integration' : 'Add bank' }}</h2>
                    <button type="button" wire:click="$set('showModal', false)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100">✕</button>
                </div>
                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Bank name *</label>
                            <input type="text" wire:model="name" class="fi-input" placeholder="HDFC / IME / Nepal Bank">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Bank code *</label>
                            <input type="text" wire:model="code" class="fi-input uppercase" placeholder="HDFC">
                            @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
                            @error('base_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
                            @foreach(['imps'=>'IMPS','neft'=>'NEFT','rtgs'=>'RTGS'] as $key => $label)
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="checkbox" wire:model="services" value="{{ $key }}" class="rounded border-slate-300 text-blue-700">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('services')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
    @endif

    @if($showAssignModal)
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
                        @forelse($vendors as $vendor)
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 text-sm hover:bg-slate-50">
                                <input type="checkbox" wire:model="assignedVendorIds" value="{{ $vendor->id }}" class="rounded border-slate-300 text-blue-700">
                                <span>
                                    <span class="font-semibold text-slate-800">{{ $vendor->business_name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $vendor->vendor_code }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="py-6 text-center text-sm text-slate-500">No partners found.</p>
                        @endforelse
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" wire:click="$set('showAssignModal', false)" class="fi-btn fi-btn-secondary">Cancel</button>
                    <button type="button" wire:click="saveAssignments" class="fi-btn fi-btn-primary">Save assignment</button>
                </div>
            </div>
        </div>
    @endif

    @if($showApisModal && $apisBank)
        <div class="fi-modal-overlay">
            <div class="fi-modal fi-modal-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ $apisBank->name }} APIs</h2>
                        <p class="text-xs text-slate-500">Integrate each API the bank provided. Vendors receive FinPay URLs, not bank credentials.</p>
                    </div>
                    <button type="button" wire:click="$set('showApisModal', false)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100">✕</button>
                </div>

                <div class="flex flex-wrap gap-2 border-b border-slate-100 px-6 py-3">
                    <button type="button" wire:click="openCreateEndpoint" class="fi-btn fi-btn-primary text-xs">+ Add API</button>
                    <button type="button" wire:click="seedMissingApis" class="fi-btn fi-btn-secondary text-xs">Load 12 templates</button>
                </div>

                @if($showEndpointForm)
                    <div class="space-y-4 border-b border-slate-100 bg-slate-50 p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">API name *</label>
                                <input type="text" wire:model="endpointName" class="fi-input" placeholder="Create Payout">
                                @error('endpointName')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Slug *</label>
                                <input type="text" wire:model="endpointSlug" class="fi-input font-mono text-sm" placeholder="payout">
                                @error('endpointSlug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                <p class="mt-1 text-[11px] text-slate-500">Vendor URL: /api/v1/bank/{{ $apisBank->code }}/{{ $endpointSlug ?: 'slug' }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">HTTP method</label>
                                <select wire:model="endpointMethod" class="fi-input">
                                    @foreach(['GET','POST','PUT','PATCH','DELETE'] as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
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
                                @forelse($requestParams as $i => $row)
                                    <div class="grid grid-cols-12 gap-2">
                                        <input type="text" wire:model="requestParams.{{ $i }}.name" class="fi-input col-span-3 text-xs" placeholder="name">
                                        <select wire:model="requestParams.{{ $i }}.type" class="fi-input col-span-2 text-xs">
                                            @foreach(['string','number','boolean','array','object'] as $t)
                                                <option value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        <label class="col-span-2 flex items-center gap-1 text-xs text-slate-600">
                                            <input type="checkbox" wire:model="requestParams.{{ $i }}.required" class="rounded border-slate-300"> Required
                                        </label>
                                        <input type="text" wire:model="requestParams.{{ $i }}.description" class="fi-input col-span-4 text-xs" placeholder="description">
                                        <button type="button" wire:click="removeRequestParam({{ $i }})" class="col-span-1 text-xs text-red-600">✕</button>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-500">No request body fields (typical for GET).</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-900">Response parameters</p>
                                <button type="button" wire:click="addResponseParam" class="text-xs font-semibold text-blue-700">+ Add field</button>
                            </div>
                            <div class="space-y-2">
                                @forelse($responseParams as $i => $row)
                                    <div class="grid grid-cols-12 gap-2">
                                        <input type="text" wire:model="responseParams.{{ $i }}.name" class="fi-input col-span-3 text-xs" placeholder="data.status">
                                        <select wire:model="responseParams.{{ $i }}.type" class="fi-input col-span-2 text-xs">
                                            @foreach(['string','number','boolean','array','object'] as $t)
                                                <option value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        <label class="col-span-2 flex items-center gap-1 text-xs text-slate-600">
                                            <input type="checkbox" wire:model="responseParams.{{ $i }}.required"> Required
                                        </label>
                                        <input type="text" wire:model="responseParams.{{ $i }}.description" class="fi-input col-span-4 text-xs" placeholder="description">
                                        <button type="button" wire:click="removeResponseParam({{ $i }})" class="col-span-1 text-xs text-red-600">✕</button>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-500">Add fields vendors should expect in the JSON response.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Sample request JSON</label>
                                <textarea wire:model="sampleRequestJson" rows="5" class="fi-input font-mono text-xs"></textarea>
                                @error('sampleRequestJson')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Sample response JSON</label>
                                <textarea wire:model="sampleResponseJson" rows="5" class="fi-input font-mono text-xs"></textarea>
                                @error('sampleResponseJson')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                @foreach(['Vendor API','Bank path','Request fields','Status',''] as $col)
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($apiEndpoints as $ep)
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="text-sm font-semibold text-slate-900">{{ $ep->name }}</p>
                                        <p class="mt-0.5 font-mono text-[11px] text-slate-500">{{ $ep->method }} /api/v1/bank/{{ $apisBank->code }}/{{ $ep->slug }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $ep->description }}</p>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $ep->bank_path ?: '—' }}</td>
                                    <td class="px-5 py-3 text-xs text-slate-600">{{ count($ep->request_params ?: []) }} in / {{ count($ep->response_params ?: []) }} out</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $ep->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $ep->is_active ? 'Active' : 'Off' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3">
                                        <button type="button" wire:click="openEditEndpoint({{ $ep->id }})" class="text-xs font-semibold text-blue-700">Edit</button>
                                        <button type="button" wire:click="toggleEndpoint({{ $ep->id }})" class="ml-2 text-xs font-semibold text-slate-600">Toggle</button>
                                        <button type="button" wire:click="deleteEndpoint({{ $ep->id }})" wire:confirm="Delete this API mapping?" class="ml-2 text-xs font-semibold text-red-600">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">No APIs yet. Load 12 templates or add each bank API manually.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
