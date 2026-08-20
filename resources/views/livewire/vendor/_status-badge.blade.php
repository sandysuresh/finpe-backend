@props(['status'])
@php
$cls = match($status) {
    'success','completed','active' => 'bg-emerald-50 text-emerald-700',
    'failed','rejected','inactive' => 'bg-red-50 text-red-600',
    'pending','processing'         => 'bg-amber-50 text-amber-700',
    default                        => 'bg-slate-100 text-slate-500',
};
@endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $cls }}">
    {{ ucfirst($status) }}
</span>
