<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — FinPay Vendor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 antialiased">

<aside id="vs" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-slate-200 bg-white"
       style="transform:translateX(-100%); transition:transform .2s;">

    {{-- Logo --}}
    <div class="flex h-[70px] shrink-0 items-center gap-3 border-b border-slate-100 px-5">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-600 text-white">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 3 20 7.5v9L12 21l-8-4.5v-9L12 3Z"/>
                <path d="m8 10 4-2 4 2-4 2-4-2Zm0 4 4 2 4-2"/>
            </svg>
        </div>
        <div class="leading-tight">
            <div class="text-[17px] font-bold text-slate-900">FinPay</div>
            <div class="text-[11px] text-slate-400">Vendor Portal</div>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4">
        @php
        $nav = [
            ['Dashboard',    'vendor.dashboard',    'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['Send Money',   'vendor.send-money',   'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
            ['Wallet',       'vendor.wallet',       'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            ['Beneficiaries','vendor.beneficiaries','M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-5a4 4 0 11-8 0 4 4 0 018 0z'],
            ['Transactions', 'vendor.transactions', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['Settlements',  'vendor.settlements',  'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
            ['API & Developer','vendor.developer',  'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
            ['Profile & KYC','vendor.profile',      'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z'],
        ];
        @endphp
        <p class="mb-2 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Menu</p>
        @foreach($nav as [$label,$route,$icon])
            @php $active = request()->routeIs($route); @endphp
            <a href="{{ route($route) }}"
               class="mb-0.5 flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition
                      {{ $active ? 'bg-violet-50 text-violet-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    {{-- User card --}}
    <div class="shrink-0 border-t border-slate-100 p-4">
        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">
                {{ strtoupper(substr(auth('vendor')->user()->business_name,0,1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="truncate text-[13px] font-semibold text-slate-800">{{ auth('vendor')->user()->business_name }}</div>
                <div class="truncate text-[11px] text-slate-400">{{ auth('vendor')->user()->vendor_code }}</div>
            </div>
            <form method="POST" action="{{ route('vendor.logout') }}">
                @csrf
                <button class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-200 hover:text-red-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Backdrop --}}
<div id="vb" onclick="closeSidebar()" class="fixed inset-0 z-30 bg-black/30 hidden lg:hidden"></div>

<div class="lg:pl-64">
    {{-- Topbar --}}
    <header class="sticky top-0 z-30 flex h-[70px] items-center justify-between border-b border-slate-200 bg-white/95 px-5 backdrop-blur">
        <button onclick="openSidebar()" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="hidden lg:block text-sm text-slate-500">{{ now()->format('l, d F Y') }}</div>
        <div class="ml-auto flex items-center gap-3">
            {{-- KYC badge --}}
            @php $kyc = auth('vendor')->user()->kyc_status; @endphp
            @if($kyc === 'pending')
                <a href="{{ route('vendor.profile') }}" class="hidden sm:flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>KYC Pending
                </a>
            @elseif($kyc === 'verified')
                <span class="hidden sm:flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>KYC Verified
                </span>
            @endif
            <button class="relative rounded-full p-2 text-slate-500 hover:bg-slate-100">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>
                </svg>
            </button>
            <div class="h-7 w-px bg-slate-200"></div>
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">
                    {{ strtoupper(substr(auth('vendor')->user()->business_name,0,1)) }}
                </div>
                <span class="hidden lg:block text-[13px] font-semibold text-slate-800">{{ auth('vendor')->user()->business_name }}</span>
            </div>
        </div>
    </header>
    <main class="p-5 lg:p-6">{{ $slot }}</main>
</div>

<script>
function openSidebar()  { document.getElementById('vs').style.transform='translateX(0)'; document.getElementById('vb').classList.remove('hidden'); }
function closeSidebar() { document.getElementById('vs').style.transform='translateX(-100%)'; document.getElementById('vb').classList.add('hidden'); }
// Always show on desktop
if(window.innerWidth >= 1024) document.getElementById('vs').style.transform='translateX(0)';
window.addEventListener('resize', () => {
    if(window.innerWidth >= 1024) { document.getElementById('vs').style.transform='translateX(0)'; document.getElementById('vb').classList.add('hidden'); }
});
</script>
@livewireScripts
</body>
</html>
