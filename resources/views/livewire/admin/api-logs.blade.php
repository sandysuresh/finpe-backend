<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">API Logs</h1>
        <p class="mt-1 text-sm text-slate-500">Bank & API → API Logs. Vendor API calls recorded by the gateway.</p>
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <input wire:model.live.debounce.300ms="search" type="text" autocomplete="off" class="fi-input w-80 text-sm" placeholder="Search vendor, endpoint, or IP...">
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @foreach(['Vendor','Method','Endpoint','Status','IP address','Time','Duration'] as $col)
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr wire:key="api-log-{{ $log->id }}" class="align-top">
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <div class="font-semibold text-slate-900">{{ $log->vendor->business_name ?? '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $log->vendor->vendor_code ?? '' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-slate-800">{{ $log->method }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $log->endpoint }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">{{ $log->status_code }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600">{{ $log->ip_address ?: '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600">{{ $log->created_at?->timezone(config('app.timezone'))->format('d-M-Y H:i') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600">{{ $log->response_time_ms !== null ? $log->response_time_ms.' ms' : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-sm text-slate-500">No API logs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $logs->links() }}</div>
    </div>
</div>
