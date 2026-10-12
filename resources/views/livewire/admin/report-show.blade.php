<div>
    <div class="mb-6">
        <a href="{{ route('admin.reports') }}" class="text-sm font-semibold text-blue-700 hover:underline">← Wallet reports</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $vendorName }}</h1>
        <p class="mt-1 font-mono text-sm text-slate-500">{{ $vendorCode }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ $labels['heading'] }}</p>
    </div>

    @include('livewire.admin.partials.wallet-report-filters', ['showVendor' => false])
    @include('livewire.admin.partials.wallet-report-table')
</div>
