@props(['label' => '', 'value' => '0', 'color' => 'slate', 'icon' => null, 'hint' => null])

@php
    $palette = [
        'teal'    => ['text' => 'text-brand-700', 'bar' => 'bg-brand-500', 'chip' => 'bg-brand-50'],
        'brand'   => ['text' => 'text-brand-700', 'bar' => 'bg-brand-500', 'chip' => 'bg-brand-50'],
        'emerald' => ['text' => 'text-emerald-700', 'bar' => 'bg-emerald-500', 'chip' => 'bg-emerald-50'],
        'sky'     => ['text' => 'text-sky-700', 'bar' => 'bg-sky-500', 'chip' => 'bg-sky-50'],
        'amber'   => ['text' => 'text-amber-700', 'bar' => 'bg-amber-500', 'chip' => 'bg-amber-50'],
        'rose'    => ['text' => 'text-rose-600', 'bar' => 'bg-rose-500', 'chip' => 'bg-rose-50'],
        'slate'   => ['text' => 'text-slate-800', 'bar' => 'bg-slate-300', 'chip' => 'bg-slate-100'],
    ];
    $c = $palette[$color] ?? $palette['slate'];
@endphp

<div class="relative overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70 transition hover:shadow-md">
    <span class="absolute inset-x-0 top-0 h-1 {{ $c['bar'] }}"></span>
    <div class="flex items-start justify-between gap-2">
        <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
        @if ($icon)
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-sm {{ $c['chip'] }}">{{ $icon }}</span>
        @endif
    </div>
    <p class="mt-1.5 text-2xl font-bold tabular-nums {{ $c['text'] }}">{{ $value }}</p>
    @if ($hint)
        <p class="mt-0.5 text-[11px] text-slate-400">{{ $hint }}</p>
    @endif
</div>
