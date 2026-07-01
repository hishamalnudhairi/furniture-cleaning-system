@extends('layouts.app')

@section('title', __('Customers'))

@section('content')
    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Customers') }}</h1>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.customers.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">+ {{ __('Add customer') }}</a>
        @endif
    </div>

    @include('partials.flash')

    <form method="GET" action="{{ route('admin.customers.index') }}" class="mb-5 flex gap-2">
        <input name="q" type="text" value="{{ $search }}" placeholder="{{ __('Search by name or phone') }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">{{ __('Search') }}</button>
    </form>

    @forelse ($customers as $customer)
        @php
            $activeOrders = $customer->orders->where('status', '!=', 'cancelled');
            $due = $activeOrders->sum('due_amount');
            $lastOrder = $customer->orders->sortByDesc('created_at')->first();
        @endphp
        <div class="mb-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-slate-800">{{ $customer->name }}</p>
                    <p class="text-sm text-slate-500">{{ $customer->phone }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $customer->wilaya }}@if ($customer->area) — {{ $customer->area }}@endif</p>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                        <span>{{ __('Orders') }}: {{ $customer->orders->count() }}</span>
                        @if ($due > 0)<span class="font-semibold text-rose-600">{{ __('Remaining') }}: {{ number_format((float) $due, 2) }}</span>@endif
                        @if ($lastOrder)<span>{{ __('Last order') }}: {{ $lastOrder->created_at->format('Y-m-d') }}</span>@endif
                    </div>
                </div>
                <a href="{{ route('admin.customers.show', $customer) }}" class="shrink-0 rounded-lg bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">{{ __('Details') }}</a>
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white p-8 text-center text-slate-500 ring-1 ring-slate-200">{{ __('No customers found.') }}</div>
    @endforelse

    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
