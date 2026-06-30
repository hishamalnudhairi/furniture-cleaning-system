@extends('layouts.app')

@section('title', __('Low stock report'))

@section('content')
    @php
        $units = ['liter' => __('Liter'), 'bottle' => __('Bottle'), 'piece' => __('Piece'), 'pack' => __('Pack'), 'kg' => __('Kg'), 'other' => __('Other')];
        $stateMeta = [
            'low' => ['label' => __('Low'), 'class' => 'bg-amber-100 text-amber-800'],
            'out' => ['label' => __('Out of stock'), 'class' => 'bg-rose-100 text-rose-700'],
        ];
    @endphp

    <a href="{{ route('admin.reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← {{ __('Back to reports') }}</a>
    <h1 class="mb-4 mt-2 text-2xl font-bold text-slate-900">{{ __('Low stock report') }}</h1>

    @forelse ($items as $item)
        @php $state = $item->stockState(); @endphp
        <div class="mb-3 flex items-start justify-between gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-slate-800">{{ $item->name }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $stateMeta[$state]['class'] ?? 'bg-slate-100' }}">{{ $stateMeta[$state]['label'] ?? $state }}</span>
                </div>
                <div class="mt-1 flex flex-wrap gap-x-4 text-xs text-slate-500">
                    <span>{{ __('Current quantity') }}: {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} {{ $units[$item->unit] ?? $item->unit }}</span>
                    <span>{{ __('Alert quantity') }}: {{ rtrim(rtrim(number_format((float) $item->min_quantity, 2), '0'), '.') }}</span>
                </div>
            </div>
            <a href="{{ route('admin.inventory.show', $item) }}" class="shrink-0 rounded-lg bg-teal-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-teal-700">{{ __('View') }}</a>
        </div>
    @empty
        <div class="rounded-xl bg-white p-8 text-center text-slate-500 ring-1 ring-slate-200">{{ __('No low stock items.') }}</div>
    @endforelse
@endsection
