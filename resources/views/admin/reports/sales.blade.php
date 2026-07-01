@extends('layouts.app')

@section('title', __('Sales report'))

@section('content')
    @php
        $payMeta = [
            'unpaid' => ['label' => __('Unpaid'), 'class' => 'bg-rose-100 text-rose-700'],
            'partial' => ['label' => __('Partial'), 'class' => 'bg-amber-100 text-amber-800'],
            'paid' => ['label' => __('Paid'), 'class' => 'bg-emerald-100 text-emerald-800'],
        ];
    @endphp

    <a href="{{ route('admin.reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back to reports') }}</a>
    <h1 class="mb-4 mt-2 text-2xl font-bold text-slate-900">{{ __('Sales report') }}</h1>

    {{-- الفلتر --}}
    <form method="GET" action="{{ route('admin.reports.sales') }}" class="mb-5 grid gap-2 sm:grid-cols-3">
        <div>
            <label class="mb-1 block text-xs text-slate-500">{{ __('From date') }}</label>
            <input name="start_date" type="date" value="{{ $start }}" class="field">
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500">{{ __('To date') }}</label>
            <input name="end_date" type="date" value="{{ $end }}" class="field">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">{{ __('Apply filter') }}</button>
        </div>
    </form>

    {{-- بطاقات الملخص --}}
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-report-stat :label="__('Orders count')" :value="$count" color="slate" />
        <x-report-stat :label="__('Subtotal')" :value="number_format($subtotal, 2)" color="slate" />
        <x-report-stat :label="__('Discount')" :value="number_format($discount, 2)" color="slate" />
        <x-report-stat :label="__('Total')" :value="number_format($total, 2)" color="teal" />
        <x-report-stat :label="__('Paid')" :value="number_format($paid, 2)" color="emerald" />
        <x-report-stat :label="__('Remaining')" :value="number_format($due, 2)" color="rose" />
        <x-report-stat :label="__('Paid orders')" :value="$paidCount" color="emerald" />
        <x-report-stat :label="__('Unpaid orders')" :value="$unpaidCount" color="rose" />
    </div>

    {{-- جدول الطلبات --}}
    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Orders in period') }}</h2>
        @forelse ($orders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0 hover:bg-slate-50">
                <div class="min-w-0">
                    <span class="font-semibold text-brand-700">{{ $order->order_number }}</span>
                    <span class="text-slate-500">· {{ $order->customer?->name }}</span>
                    <p class="text-xs text-slate-400">{{ $order->created_at->format('Y-m-d') }}</p>
                </div>
                <div class="flex items-center gap-2 text-end">
                    <span class="font-semibold text-slate-700">{{ number_format((float) $order->total, 2) }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $payMeta[$order->payment_status]['class'] ?? 'bg-slate-100' }}">{{ $payMeta[$order->payment_status]['label'] ?? $order->payment_status }}</span>
                </div>
            </a>
        @empty
            <p class="text-sm text-slate-400">{{ __('No orders in this period.') }}</p>
        @endforelse
    </div>
@endsection
