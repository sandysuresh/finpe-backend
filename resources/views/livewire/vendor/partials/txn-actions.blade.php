@if(!empty($id) && !empty($reference) && ($type ?? 'payout') === 'payout')
    <span style="display:inline-flex; align-items:center; gap:6px; width:auto;">
        @if(!empty($livewire))
            <button type="button" wire:click.stop="show({{ (int) $id }})" class="fi-btn fi-btn-sm fi-btn-primary" style="width:auto; height:28px; padding:0 8px; font-size:11px;">View</button>
        @else
            <a href="{{ route('vendor.payout-api', ['view' => $id]) }}" class="fi-btn fi-btn-sm fi-btn-primary" style="width:auto; height:28px; padding:0 8px; font-size:11px; text-decoration:none;">View</a>
        @endif
        <a href="{{ route('vendor.payout-transactions.receipt', $reference) }}" class="fi-btn fi-btn-sm fi-btn-secondary" style="width:auto; height:28px; padding:0 8px; font-size:11px; text-decoration:none;">PDF</a>
    </span>
@endif
