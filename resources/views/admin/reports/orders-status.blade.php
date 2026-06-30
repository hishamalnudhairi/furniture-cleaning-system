@extends('layouts.app')

@section('title', __('Orders by status'))

@section('content')
    @php
        $meta = [
            'new' => ['label' => __('New'), 'class' => 'bg-slate-100 text-slate-700'],
            'cleaning' => ['label' => __('Cleaning'), 'class' => 'bg-sky-100 text-sky-800'],
            'ready' => ['label' => __('Ready for delivery'), 'class' => 'bg-emerald-100 text-emerald-800'],
            'delivered' => ['label' => __('Delivered'), 'class' => 'bg-teal-100 text-teal-800'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-rose-100 text-rose-700'],
        ];
    @endphp

    <a href="{{ route('admin.reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← {{ __('Back to reports') }}</a>
    <h1 class="mb-4 mt-2 text-2xl font-bold text-slate-900">{{ __('Orders by status') }}</h1>

    <div class="space-y-3">
        @foreach ($rows as $status => $row)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <span class="rounded-full px-3 py-1 text-sm font-medium {{ $meta[$status]['class'] ?? 'bg-slate-100' }}">{{ $meta[$status]['label'] ?? $status }}</span>
                <div class="flex items-center gap-6 text-sm">
                    <span class="text-slate-500">{{ __('Count') }}: <span class="font-bold text-slate-800">{{ $row['count'] }}</span></span>
                    <span class="text-slate-500">{{ __('Total') }}: <span class="font-bold text-teal-700">{{ number_format($row['total'], 2) }}</span></span>
                </div>
            </div>
        @endforeach
    </div>
@endsection
