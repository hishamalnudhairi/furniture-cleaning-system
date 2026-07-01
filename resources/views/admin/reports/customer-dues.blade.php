@extends('layouts.app')

@section('title', __('Customer dues report'))

@section('content')
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back to reports') }}</a>
    <h1 class="mb-4 mt-2 text-2xl font-bold text-slate-900">{{ __('Customer dues report') }}</h1>

    @forelse ($rows as $row)
        <div class="mb-3 flex items-start justify-between gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="min-w-0">
                <p class="font-medium text-slate-800">{{ $row->customer?->name ?? '—' }}</p>
                <p class="text-sm text-slate-500">{{ $row->customer?->phone }}</p>
                <div class="mt-1 flex flex-wrap gap-x-4 text-xs text-slate-400">
                    <span>{{ __('Orders with balance') }}: {{ $row->orders_count }}</span>
                    <span>{{ __('Last order') }}: {{ \Illuminate\Support\Carbon::parse($row->last_order)->format('Y-m-d') }}</span>
                </div>
            </div>
            <span class="shrink-0 text-lg font-bold text-rose-600">{{ number_format((float) $row->total_due, 2) }}</span>
        </div>
    @empty
        <div class="rounded-xl bg-white p-8 text-center text-slate-500 ring-1 ring-slate-200">{{ __('No customer dues.') }}</div>
    @endforelse
@endsection
