<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Commission Settlement</h1>
        <p class="mt-1 text-sm text-slate-500">Draft, approve, or reject. Approval records the decision only and does not post a wallet entry.</p>
    </div>
    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    @if($canSettle)
        <form wire:submit="createDraft" class="fi-card mb-5 grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-4">
            <select wire:model.live="vendorId" class="fi-input text-sm"><option value="">Vendor</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>@endforeach</select>
            <input wire:model.live="periodStart" type="date" class="fi-input text-sm">
            <input wire:model.live="periodEnd" type="date" class="fi-input text-sm">
            <button type="submit" class="fi-btn fi-btn-primary">Create draft</button>
            <div class="md:col-span-4">
                @forelse($eligible as $entry)
                    <label class="mr-4 inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="selected" value="{{ $entry->id }}">
                        {{ $entry->source_reference }} · {{ $entry->commission_amount }}
                    </label>
                @empty
                    <p class="text-sm text-slate-500">Choose a vendor and period to list unsettled entries.</p>
                @endforelse
                @error('selected')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </form>
    @endif

    <div class="fi-card mb-4 px-5 py-4">
        <select wire:model.live="filterStatus" class="fi-input w-48 text-sm"><option value="">All statuses</option><option value="draft">Draft</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select>
    </div>

    <div class="fi-card overflow-hidden">
        <table class="min-w-full">
            <thead><tr>@foreach(['Reference','Vendor','Period','Total','Status','Created',''] as $col)<th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($settlements as $settlement)
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold">{{ $settlement->reference }}</td>
                        <td class="px-4 py-3 text-sm">{{ $settlement->vendor->business_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $settlement->period_start?->format('d M Y') }} – {{ $settlement->period_end?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $settlement->total_amount }}</td>
                        <td class="px-4 py-3 text-sm">{{ $settlement->status }}</td>
                        <td class="px-4 py-3 text-sm">{{ $settlement->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            @if($canApprove && $settlement->status === 'draft')
                                <button type="button" wire:click="approve({{ $settlement->id }})" class="text-emerald-700">Approve</button>
                                <button type="button" wire:click="reject({{ $settlement->id }})" class="ml-3 text-red-700">Reject</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">No commission settlements.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $settlements->links() }}</div>
    </div>
</div>
