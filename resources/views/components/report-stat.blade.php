@props(['label' => '', 'value' => '0', 'color' => 'slate'])

@php
    $colors = [
        'teal' => 'text-teal-700',
        'emerald' => 'text-emerald-700',
        'sky' => 'text-sky-700',
        'amber' => 'text-amber-700',
        'rose' => 'text-rose-600',
        'slate' => 'text-slate-800',
    ];
    $valueClass = $colors[$color] ?? $colors['slate'];
@endphp

<div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200">
    <p class="text-xs text-slate-400">{{ $label }}</p>
    <p class="mt-1 text-xl font-bold {{ $valueClass }}">{{ $value }}</p>
</div>
