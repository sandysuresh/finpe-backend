@if(!empty($id) && !empty($reference) && ($type ?? 'payout') === 'payout')
    @php $source = ($source ?? 'transaction') === 'alliancepe' ? 'alliancepe' : 'transaction'; @endphp
    <span style="display:inline-flex; align-items:center; gap:6px; width:auto;" onclick="event.stopPropagation()">
        @if(!empty($livewire))
            <button type="button" wire:click.stop="show({{ (int) $id }}, '{{ $source }}')" class="fi-btn fi-btn-sm fi-btn-primary" style="width:auto; height:28px; padding:0 8px; font-size:11px;">View</button>
        @else
            <a href="{{ route('admin.payout-transactions', ['view' => $id, 'source' => $source]) }}" class="fi-btn fi-btn-sm fi-btn-primary" style="width:auto; height:28px; padding:0 8px; font-size:11px; text-decoration:none;">View</a>
        @endif
        @if($source !== 'alliancepe')
            <a href="{{ route('admin.payout-transactions.receipt', $reference) }}" class="fi-btn fi-btn-sm fi-btn-secondary" style="width:auto; height:28px; padding:0 8px; font-size:11px; text-decoration:none;">PDF</a>
        @endif
    </span>
@endif
