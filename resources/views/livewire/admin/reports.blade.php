<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Wallet reports</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $labels['heading'] }}</p>
    </div>

    @include('livewire.admin.partials.wallet-report-filters', ['showVendor' => true])
    @include('livewire.admin.partials.wallet-report-table')
</div>
