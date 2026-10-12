<div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-6">
    @foreach([
        [$labels['opening'], $summary['opening'], 'text-slate-900'],
        ['Total credit', $summary['credit'], 'text-emerald-700'],
        ['Total debit', $summary['debit'], 'text-red-700'],
        ['Refunds', $summary['refunds'], 'text-amber-700'],
        ['Transaction count', $summary['count'], 'text-slate-900'],
        [$labels['closing'], $summary['closing'], 'text-blue-800'],
    ] as [$label, $value, $color])
        <div class="fi-card p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
            <p class="mt-1 text-xl font-bold {{ $color }}">
                @if($label === 'Transaction count')
                    {{ number_format($value) }}
                @else
                    ₹{{ number_format($value, 2) }}
                @endif
            </p>
        </div>
    @endforeach
</div>

<p class="mb-5 text-sm text-slate-500">{{ $note }}</p>

<div class="fi-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    @foreach(['Date/time','Vendor','Reference','Type','Payout provider','Description','Credit','Debit','Running balance','Status'] as $col)
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($lines as $line)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600">{{ $line['at']?->timezone(config('app.timezone'))->format('d M Y H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">{{ $line['vendor_name'] }}</p>
                            <p class="font-mono text-xs text-slate-500">{{ $line['vendor_code'] }} · {{ $line['vendor_id'] }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-700">
                            {{ $line['reference'] !== '' ? $line['reference'] : '—' }}
                            @if($line['txn_reference'] !== '' && $line['txn_reference'] !== $line['reference'])
                                <p class="text-slate-400">{{ $line['txn_reference'] }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ $line['entry'] }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $line['provider'] !== '' ? $line['provider'] : '—' }}</td>
                        <td class="max-w-[220px] px-4 py-3 text-xs text-slate-600">{{ $line['description'] !== '' ? $line['description'] : '—' }}</td>
                        <td class="px-4 py-3 text-sm text-emerald-700">{{ $line['credit'] > 0 ? '₹'.number_format($line['credit'], 2) : '—' }}</td>
                        <td class="px-4 py-3 text-sm text-red-700">{{ $line['debit'] > 0 ? '₹'.number_format($line['debit'], 2) : '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-slate-900">₹{{ number_format($line['running'], 2) }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $line['txn_status'] !== '' ? ucfirst($line['txn_status']) : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-5 py-12 text-center text-sm text-slate-500">No ledger rows match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lines->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $lines->links() }}</div>
    @endif
</div>
