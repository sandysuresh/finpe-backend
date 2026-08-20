<div class="relative" wire:poll.15s wire:click.outside="close">
    <button type="button" wire:click="toggle" class="relative rounded-full p-2 text-slate-300 hover:bg-slate-800 hover:text-white">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>
        </svg>
        @if($unread > 0)
            <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white">
                {{ $unread > 99 ? '99+' : $unread }}
            </span>
        @endif
    </button>

    @if($open)
        <div class="absolute right-0 top-12 z-50 w-[22rem] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <p class="text-sm font-semibold text-slate-800">Notifications</p>
                @if($unread > 0)
                    <button type="button" wire:click="markAllRead" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                        Mark all read
                    </button>
                @endif
            </div>

            <div class="max-h-96 overflow-y-auto">
                @forelse($notifications as $n)
                    <button
                        type="button"
                        wire:click="openNotification({{ $n->id }})"
                        class="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-slate-50 {{ $n->isUnread() ? 'bg-blue-50/60' : '' }}"
                    >
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $n->type === 'kyc' ? 'bg-violet-100 text-violet-700' : 'bg-emerald-100 text-emerald-700' }}">
                            @if($n->type === 'kyc')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            @else
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-slate-800">{{ $n->title }}</span>
                                @if($n->isUnread())
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-blue-600"></span>
                                @endif
                            </span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ $n->body }}</span>
                            <span class="mt-1 block text-[11px] text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                        </span>
                    </button>
                @empty
                    <div class="px-4 py-10 text-center">
                        <p class="text-sm font-medium text-slate-600">No notifications yet</p>
                        <p class="mt-1 text-xs text-slate-400">Vendor KYC and wallet requests will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
