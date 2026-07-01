@extends('layouts.app')

@section('title', __('Orders'))

@section('content')
    @php
        $statusMeta = [
            'new' => ['label' => __('New'), 'class' => 'bg-slate-100 text-slate-700', 'dot' => 'bg-slate-400'],
            'cleaning' => ['label' => __('Cleaning'), 'class' => 'bg-sky-100 text-sky-800', 'dot' => 'bg-sky-500'],
            'ready' => ['label' => __('Ready for delivery'), 'class' => 'bg-emerald-100 text-emerald-800', 'dot' => 'bg-emerald-500'],
            'delivered' => ['label' => __('Delivered'), 'class' => 'bg-brand-100 text-brand-800', 'dot' => 'bg-brand-500'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-rose-100 text-rose-700', 'dot' => 'bg-rose-500'],
        ];
        $payMeta = [
            'unpaid' => ['label' => __('Unpaid'), 'class' => 'bg-rose-100 text-rose-700', 'dot' => 'bg-rose-500'],
            'partial' => ['label' => __('Partial'), 'class' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-500'],
            'paid' => ['label' => __('Paid'), 'class' => 'bg-emerald-100 text-emerald-800', 'dot' => 'bg-emerald-500'],
        ];
        $statusTabs = ['' => __('All'), 'new' => __('New'), 'cleaning' => __('Cleaning'), 'ready' => __('Ready for delivery'), 'delivered' => __('Delivered'), 'cancelled' => __('Cancelled')];
        $payTabs = ['unpaid' => __('Unpaid'), 'partial' => __('Partial'), 'paid' => __('Paid')];
        $hasFilter = $status || $payment || $search;
    @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Orders') }}</h1>
        <span class="rounded-full bg-white px-3 py-1 text-sm font-medium text-slate-500 ring-1 ring-slate-200">
            {{ $orders->total() }} {{ __('Orders count') }}
        </span>
    </div>

    @include('partials.flash')

    {{-- شريط الأدوات: بحث + فلاتر داخل بطاقة واحدة --}}
    <div class="mb-5 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200 sm:p-4">
        {{-- البحث --}}
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex gap-2">
            @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            @if ($payment)<input type="hidden" name="payment" value="{{ $payment }}">@endif
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 flex items-center text-slate-400 ltr:left-3 rtl:right-3">🔍</span>
                <input name="q" type="search" value="{{ $search }}"
                       placeholder="{{ __('Search by order no., name, or phone.') }}"
                       class="field ltr:pl-10 rtl:pr-10">
            </div>
            <button type="submit" class="btn btn-dark shrink-0">{{ __('Search') }}</button>
        </form>

        {{-- فلاتر حالة الطلب --}}
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($statusTabs as $value => $label)
                <a href="{{ route('admin.orders.index', array_filter(['status' => $value, 'payment' => $payment, 'q' => $search])) }}"
                   class="rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ (string) $status === (string) $value ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- فلاتر حالة الدفع --}}
        <div class="mt-2 flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-medium text-slate-400">💳 {{ __('Payment status') }}:</span>
            @foreach ($payTabs as $value => $label)
                <a href="{{ route('admin.orders.index', array_filter(['status' => $status, 'payment' => ($payment === $value ? null : $value), 'q' => $search])) }}"
                   class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition {{ (string) $payment === (string) $value ? 'bg-slate-800 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $payMeta[$value]['dot'] }}"></span>{{ $label }}
                </a>
            @endforeach
            @if ($hasFilter)
                <a href="{{ route('admin.orders.index') }}"
                   class="ltr:ml-auto rtl:mr-auto text-xs font-medium text-slate-400 underline-offset-2 hover:text-rose-600 hover:underline">
                    ✕ {{ __('Clear filters') }}
                </a>
            @endif
        </div>
    </div>

    @forelse ($orders as $order)
        <a href="{{ route('admin.orders.show', $order) }}"
           class="mb-3 block rounded-2xl bg-white p-4 shadow-sm ring-1 transition hover:shadow-md hover:ring-brand-300 {{ $order->status === 'ready' ? 'ring-2 ring-emerald-300' : 'ring-slate-200' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-brand-700">{{ $order->order_number }}</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusMeta[$order->status]['class'] ?? 'bg-slate-100' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $statusMeta[$order->status]['dot'] ?? 'bg-slate-400' }}"></span>{{ $statusMeta[$order->status]['label'] ?? $order->status }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $payMeta[$order->payment_status]['class'] ?? 'bg-slate-100' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $payMeta[$order->payment_status]['dot'] ?? 'bg-slate-400' }}"></span>{{ $payMeta[$order->payment_status]['label'] ?? $order->payment_status }}
                        </span>
                    </div>
                    <p class="mt-2 font-semibold text-slate-800">{{ $order->customer?->name }}</p>
                    <p class="text-sm text-slate-500" dir="ltr">{{ $order->customer?->phone }}</p>
                </div>
                <div class="shrink-0 text-end">
                    <p class="text-lg font-bold text-slate-900 tabular-nums">{{ number_format((float) $order->total, 2) }}</p>
                    <p class="text-[11px] text-slate-400">{{ __('Total') }}</p>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 border-t border-slate-100 pt-2 text-sm">
                <span class="text-slate-500">{{ __('Paid') }}: <span class="font-medium text-slate-700 tabular-nums">{{ number_format((float) $order->paid_amount, 2) }}</span></span>
                @if ((float) $order->due_amount > 0)
                    <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-0.5 font-semibold text-rose-600 tabular-nums">
                        {{ __('Remaining') }}: {{ number_format((float) $order->due_amount, 2) }}
                    </span>
                @else
                    <span class="text-xs font-medium text-emerald-600">✓ {{ __('Paid') }}</span>
                @endif
                <span class="text-xs text-slate-400 ltr:ml-auto rtl:mr-auto">🕒 {{ $order->created_at->format('Y-m-d H:i') }}</span>
            </div>
        </a>
    @empty
        <div class="rounded-2xl bg-white p-10 text-center ring-1 ring-slate-200">
            <p class="text-4xl">📭</p>
            <p class="mt-3 font-medium text-slate-500">{{ __('No orders found.') }}</p>
            @if ($hasFilter)
                <a href="{{ route('admin.orders.index') }}" class="mt-3 inline-block text-sm font-medium text-brand-600 hover:underline">✕ {{ __('Clear filters') }}</a>
            @endif
        </div>
    @endforelse

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
