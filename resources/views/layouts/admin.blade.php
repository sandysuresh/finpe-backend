<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - FinPay Gateway</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen antialiased" style="--fi-accent:#1d4ed8">
    <header class="sticky top-0 z-50 border-b border-slate-800 bg-slate-900">
        <div class="mx-auto flex h-[70px] max-w-[1600px] items-center px-5">
            <a href="{{ route('admin.dashboard') }}" class="mr-8 flex min-w-fit items-center gap-2.5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3 20 7.5v9L12 21l-8-4.5v-9L12 3Z"/>
                        <path d="m8 10 4-2 4 2-4 2-4-2Zm0 4 4 2 4-2"/>
                    </svg>
                </div>
                <div class="leading-tight">
                    <div class="text-[20px] font-bold tracking-tight text-white">FinPay</div>
                    <div class="text-[11px] font-medium text-slate-300">Admin Gateway</div>
                </div>
            </a>

            <nav class="hidden flex-1 items-center gap-1 xl:flex">
                @php
                    $menus = [
                        ['Dashboard',       route('admin.dashboard'),       request()->routeIs('admin.dashboard')],
                        ['Vendors',         route('admin.vendors'),         request()->routeIs('admin.vendors*')],
                        ['Wallet Requests', route('admin.wallet-requests'), request()->routeIs('admin.wallet-requests')],
                        ['Transactions',    route('admin.transactions'),    request()->routeIs('admin.transactions')],
                        ['Settlements',     '#',                            false],
                        ['Reports',         '#',                            false],
                        ['API Logs',        '#',                            false],
                    ];
                @endphp
                @foreach ($menus as [$label, $url, $active])
                    <a href="{{ $url }}" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-[13px] font-semibold {{ $active ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-3">
                <button class="hidden rounded-full p-2 text-slate-300 hover:bg-slate-800 hover:text-white sm:block" title="Theme">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                    </svg>
                </button>
                @livewire('admin.notification-bell')

                <div class="hidden h-8 w-px bg-slate-700 sm:block"></div>

                <div class="group relative">
                    <button class="flex items-center gap-2.5 rounded-lg px-1.5 py-1 hover:bg-slate-800">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">
                            {{ strtoupper(substr(auth('admin')->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden text-left lg:block">
                            <span class="block text-[13px] font-semibold text-white">{{ auth('admin')->user()->name }}</span>
                            <span class="block text-[11px] text-slate-300">{{ ucfirst(auth('admin')->user()->role) }}</span>
                        </span>
                        <svg class="hidden h-4 w-4 text-slate-400 lg:block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25-4.5a.75.75 0 0 1-1.08-1.06l4.25-4.5a.75.75 0 0 1 1.06-.02Z" clip-rule="evenodd"/></svg>
                    </button>
                    <div class="invisible absolute right-0 top-12 w-48 translate-y-1 rounded-xl border border-slate-200 bg-white p-1.5 opacity-0 shadow-xl transition group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100">
                        <div class="px-3 py-2 text-xs text-slate-500">Account</div>
                        <a href="#" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Profile</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-800 xl:hidden">
            <nav class="fi-scroll flex gap-1 overflow-x-auto px-4 py-2">
                @foreach ($menus as [$label, $url, $active])
                    <a href="{{ $url }}" class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-semibold {{ $active ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-[1600px] px-5 py-7">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
