@extends('layouts.app')

@section('title', __('Customers'))

@section('content')
    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Customers') }}</h1>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary text-sm">+ {{ __('Add customer') }}</a>
        @endif
    </div>

    @include('partials.flash')

    <form method="GET" action="{{ route('admin.customers.index') }}" class="mb-5 flex gap-2">
        <div class="relative flex-1">
            <span class="pointer-events-none absolute inset-y-0 flex items-center text-slate-400 ltr:left-3 rtl:right-3">🔍</span>
            <input name="q" type="search" value="{{ $search }}" placeholder="{{ __('Search by name or phone') }}"
                   class="field ltr:pl-10 rtl:pr-10">
        </div>
        <button type="submit" class="btn btn-dark shrink-0">{{ __('Search') }}</button>
    </form>

    @forelse ($customers as $customer)
        @php
            $activeOrders = $customer->orders->where('status', '!=', 'cancelled');
            $due = $activeOrders->sum('due_amount');
            $lastOrder = $customer->orders->sortByDesc('created_at')->first();
        @endphp
        <a href="{{ route('admin.customers.show', $customer) }}"
           class="mb-3 block rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md hover:ring-brand-300">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-slate-800">{{ $customer->name }}</p>
                    <p class="text-sm text-slate-500" dir="ltr">{{ $customer->phone }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $customer->wilaya }}@if ($customer->area) — {{ $customer->area }}@endif</p>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                        <span>{{ __('Orders') }}: {{ $customer->orders->count() }}</span>
                        @if ($due > 0)<span class="font-semibold text-rose-600 tabular-nums">{{ __('Remaining') }}: {{ number_format((float) $due, 2) }}</span>@endif
                        @if ($lastOrder)<span>{{ __('Last order') }}: {{ $lastOrder->created_at->format('Y-m-d') }}</span>@endif
                    </div>
                </div>
                <span class="shrink-0 self-center text-slate-300"><span class="ib-flip">→</span></span>
            </div>
        </a>
    @empty
        <x-empty icon="👤" :message="__('No customers found.')" />
    @endforelse

    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
