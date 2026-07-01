@props(['label' => '', 'value' => '0', 'color' => 'slate'])

@php
    $palette = [
        'teal'    => ['text' => 'text-brand-700', 'bar' => 'bg-brand-500'],
        'brand'   => ['text' => 'text-brand-700', 'bar' => 'bg-brand-500'],
        'emerald' => ['text' => 'text-emerald-700', 'bar' => 'bg-emerald-500'],
        'sky'     => ['text' => 'text-sky-700', 'bar' => 'bg-sky-500'],
        'amber'   => ['text' => 'text-amber-700', 'bar' => 'bg-amber-500'],
        'rose'    => ['text' => 'text-rose-600', 'bar' => 'bg-rose-500'],
        'slate'   => ['text' => 'text-slate-800', 'bar' => 'bg-slate-300'],
    ];
    $c = $palette[$color] ?? $palette['slate'];
@endphp

<div class="relative overflow-hidden rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200/70 transition hover:shadow-md">
    <span class="absolute inset-x-0 top-0 h-1 {{ $c['bar'] }}"></span>
    <p class="text-xs text-slate-400">{{ $label }}</p>
    <p class="mt-1 text-xl font-bold {{ $c['text'] }}">{{ $value }}</p>
</div>
