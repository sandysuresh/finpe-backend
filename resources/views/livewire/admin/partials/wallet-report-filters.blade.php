<div class="fi-card mb-5 px-5 py-4">
    <div style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px;">
        @if($showVendor)
            <div style="width:240px; max-width:100%;">
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Vendor</label>
                <select wire:model="vendor" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                    <option value="">All vendors</option>
                    @foreach($vendors as $item)
                        <option value="{{ $item->id }}">{{ $item->business_name }} ({{ $item->vendor_code }})</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">From</label>
            <input type="date" wire:model="dateFrom" class="fi-input text-sm" style="height:40px; width:160px; padding:0 10px;">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">To</label>
            <input type="date" wire:model="dateTo" class="fi-input text-sm" style="height:40px; width:160px; padding:0 10px;">
        </div>
        <div style="width:180px; max-width:100%;">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Payout provider</label>
            <select wire:model="provider" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                <option value="">All providers</option>
                @foreach($providers as $item)
                    <option value="{{ $item['code'] }}">{{ $item['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div style="width:180px; max-width:100%;">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Type</label>
            <select wire:model="entry" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                <option value="">All types</option>
                @foreach($entries as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div style="width:160px; max-width:100%;">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Status</label>
            <select wire:model="status" class="fi-input text-sm" style="height:40px; padding:0 12px;">
                <option value="">All statuses</option>
                @foreach($statuses as $item)
                    <option value="{{ $item }}">{{ ucfirst($item) }}</option>
                @endforeach
            </select>
        </div>
        <div style="width:240px; max-width:100%;">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Search</label>
            <input type="text" wire:model="search" class="fi-input text-sm" style="height:40px;" placeholder="Vendor, reference, description">
        </div>
        <div style="display:flex; gap:8px; margin-left:auto;">
            <button type="button" wire:click="applyFilters" class="fi-btn fi-btn-primary">Apply</button>
            <button type="button" wire:click="resetFilters" class="fi-btn fi-btn-secondary">Reset</button>
            @if(auth('admin')->user()->hasPermission('reports', 'export'))
                <button type="button" wire:click="exportExcel" class="fi-btn fi-btn-secondary">Export Excel</button>
            @endif
        </div>
    </div>
</div>
