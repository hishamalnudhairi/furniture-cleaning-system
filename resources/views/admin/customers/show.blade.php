@extends('layouts.app')

@section('title', $customer->name)

@section('content')
    @php
        $typeLabels = ['individual' => __('Individual'), 'company' => __('Company'), 'mosque' => __('Mosque'), 'organization' => __('Organization')];
        $statusMeta = [
            'new' => ['label' => __('New'), 'class' => 'bg-slate-100 text-slate-700'],
            'cleaning' => ['label' => __('Cleaning'), 'class' => 'bg-sky-100 text-sky-800'],
            'ready' => ['label' => __('Ready for delivery'), 'class' => 'bg-emerald-100 text-emerald-800'],
            'delivered' => ['label' => __('Delivered'), 'class' => 'bg-brand-100 text-brand-800'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-rose-100 text-rose-700'],
        ];
        $mapUrl = $customer->location_url ?: (($customer->latitude && $customer->longitude)
            ? 'https://www.google.com/maps?q='.$customer->latitude.','.$customer->longitude : null);
    @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('admin.customers.index') }}" class="text-sm text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back to list') }}</a>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.customers.edit', $customer) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Edit') }}</a>
        @endif
    </div>

    <h1 class="mb-4 text-2xl font-bold text-slate-900">{{ $customer->name }}</h1>

    @include('partials.flash')

    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-400">{{ __('Phone number') }}</dt><dd class="font-medium text-slate-800" dir="ltr">{{ $customer->phone }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Customer type') }}</dt><dd class="text-slate-800">{{ $typeLabels[$customer->customer_type] ?? '—' }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Wilaya') }}</dt><dd class="text-slate-800">{{ $customer->wilaya ?: '—' }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Area / Village') }}</dt><dd class="text-slate-800">{{ $customer->area ?: '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-400">{{ __('Detailed address') }}</dt><dd class="text-slate-800">{{ $customer->address ?: '—' }}</dd></div>
        </dl>
        @if ($mapUrl)
            <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="mt-3 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">📍 {{ __('Open in Google Maps') }}</a>
        @endif
    </section>

    {{-- ملخص مالي --}}
    <section class="mb-4 grid grid-cols-3 gap-3">
        <x-report-stat :label="__('Orders')" :value="$customer->orders->count()" color="slate" />
        <x-report-stat :label="__('Total paid')" :value="number_format($totalPaid, 2)" color="emerald" />
        <x-report-stat :label="__('Remaining')" :value="number_format($totalDue, 2)" color="rose" />
    </section>

    {{-- الطلبات السابقة --}}
    <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Orders') }}</h2>
        @forelse ($customer->orders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0 hover:bg-slate-50">
                <div>
                    <span class="font-semibold text-brand-700">{{ $order->order_number }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusMeta[$order->status]['class'] ?? 'bg-slate-100' }}">{{ $statusMeta[$order->status]['label'] ?? $order->status }}</span>
                    <p class="text-xs text-slate-400">{{ $order->created_at->format('Y-m-d') }}</p>
                </div>
                <div class="text-end">
                    <span class="font-semibold text-slate-700">{{ number_format((float) $order->total, 2) }}</span>
                    @if ((float) $order->due_amount > 0)<p class="text-xs text-rose-600">{{ __('Remaining') }}: {{ number_format((float) $order->due_amount, 2) }}</p>@endif
                </div>
            </a>
        @empty
            <p class="text-sm text-slate-400">{{ __('No orders yet.') }}</p>
        @endforelse
    </section>
@endsection
