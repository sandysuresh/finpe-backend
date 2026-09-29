<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $current['title'] }}</h1>
        <p class="mt-1 text-sm text-slate-500">Users → {{ $current['title'] }}. {{ $current['text'] }}</p>
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
        @foreach($sections as $key => $item)
            <a href="{{ route($key === 'all' ? 'admin.app-users.list' : 'admin.app-users.'.$key) }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $section === $key ? 'bg-blue-600 text-white' : 'bg-white text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50' }}">
                {{ $item['title'] }}
            </a>
        @endforeach
    </div>

    <div class="fi-card mb-5 px-5 py-4">
        <input type="text" disabled class="fi-input w-80 text-sm" placeholder="Search will be available after app registration">
    </div>

    <div class="fi-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        @foreach($current['columns'] as $col)
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="{{ count($current['columns']) }}" class="px-5 py-16 text-center text-sm text-slate-500">No app users yet. They will show here after registration.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
