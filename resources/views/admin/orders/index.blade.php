@extends('layouts.app')

@section('title', __('Orders'))

@section('content')
    @php
        $statusMeta = [
            'new' => ['label' => __('New'), 'class' => 'bg-slate-100 text-slate-700'],
            'cleaning' => ['label' => __('Cleaning'), 'class' => 'bg-sky-100 text-sky-800'],
            'ready' => ['label' => __('Ready for delivery'), 'class' => 'bg-emerald-100 text-emerald-800'],
            'delivered' => ['label' => __('Delivered'), 'class' => 'bg-brand-100 text-brand-800'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-rose-100 text-rose-700'],
        ];
        $payMeta = [
            'unpaid' => ['label' => __('Unpaid'), 'class' => 'bg-rose-100 text-rose-700'],
            'partial' => ['label' => __('Partial'), 'class' => 'bg-amber-100 text-amber-800'],
            'paid' => ['label' => __('Paid'), 'class' => 'bg-emerald-100 text-emerald-800'],
        ];
        $statusTabs = ['' => __('All'), 'new' => __('New'), 'cleaning' => __('Cleaning'), 'ready' => __('Ready for delivery'), 'delivered' => __('Delivered'), 'cancelled' => __('Cancelled')];
        $payTabs = ['unpaid' => __('Unpaid'), 'partial' => __('Partial'), 'paid' => __('Paid')];
    @endphp

    <h1 class="mb-4 text-2xl font-bold text-slate-900">{{ __('Orders') }}</h1>

    @include('partials.flash')

    {{-- فلاتر حالة الطلب --}}
    <div class="mb-2 flex flex-wrap gap-2">
        @foreach ($statusTabs as $value => $label)
            <a href="{{ route('admin.orders.index', array_filter(['status' => $value, 'payment' => $payment, 'q' => $search])) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ (string) $status === (string) $value ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- فلاتر حالة الدفع --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($payTabs as $value => $label)
            <a href="{{ route('admin.orders.index', array_filter(['status' => $status, 'payment' => ($payment === $value ? null : $value), 'q' => $search])) }}"
               class="rounded-full px-3 py-1 text-xs font-medium transition {{ (string) $payment === (string) $value ? 'bg-slate-800 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100' }}">
                💳 {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- البحث --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-5 flex gap-2">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        @if ($payment)<input type="hidden" name="payment" value="{{ $payment }}">@endif
        <input name="q" type="text" value="{{ $search }}"
               placeholder="{{ __('Search by order no., name, or phone.') }}"
               class="field">
        <button type="submit" class="btn btn-dark shrink-0">{{ __('Search') }}</button>
    </form>

    @forelse ($orders as $order)
        <div class="mb-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 {{ $order->status === 'ready' ? 'ring-2 ring-emerald-300' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-brand-700">{{ $order->order_number }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusMeta[$order->status]['class'] ?? 'bg-slate-100' }}">{{ $statusMeta[$order->status]['label'] ?? $order->status }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $payMeta[$order->payment_status]['class'] ?? 'bg-slate-100' }}">{{ $payMeta[$order->payment_status]['label'] ?? $order->payment_status }}</span>
                    </div>
                    <p class="mt-1 font-medium text-slate-800">{{ $order->customer?->name }}</p>
                    <p class="text-sm text-slate-500">{{ $order->customer?->phone }}</p>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                        <span class="text-slate-600">{{ __('Total') }}: <span class="font-semibold">{{ number_format((float) $order->total, 2) }}</span></span>
                        <span class="text-slate-600">{{ __('Paid') }}: {{ number_format((float) $order->paid_amount, 2) }}</span>
                        @if ((float) $order->due_amount > 0)
                            <span class="font-semibold text-rose-600">{{ __('Remaining') }}: {{ number_format((float) $order->due_amount, 2) }}</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-slate-400">🕒 {{ $order->created_at->format('Y-m-d H:i') }}</p>
                </div>
                <a href="{{ route('admin.orders.show', $order) }}"
                   class="shrink-0 rounded-lg bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">{{ __('Details') }}</a>
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white p-8 text-center text-slate-500 ring-1 ring-slate-200">{{ __('No orders found.') }}</div>
    @endforelse

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
