@extends('layouts.app')

@section('title', __('Inventory'))

@section('content')
    @php
        $units = ['liter' => __('Liter'), 'bottle' => __('Bottle'), 'piece' => __('Piece'), 'pack' => __('Pack'), 'kg' => __('Kg'), 'other' => __('Other')];
        $stateMeta = [
            'ok' => ['label' => __('In stock'), 'class' => 'bg-emerald-100 text-emerald-800'],
            'low' => ['label' => __('Low'), 'class' => 'bg-amber-100 text-amber-800'],
            'out' => ['label' => __('Out of stock'), 'class' => 'bg-rose-100 text-rose-700'],
        ];
        $tabs = ['' => __('All'), 'low' => __('Low'), 'out' => __('Out of stock'), 'active' => __('Active'), 'inactive' => __('Inactive')];
    @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Inventory') }}</h1>
        <a href="{{ route('admin.inventory.create') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">+ {{ __('Add item') }}</a>
    </div>

    @include('partials.flash')

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($tabs as $value => $label)
            <a href="{{ route('admin.inventory.index', array_filter(['filter' => $value, 'q' => $search])) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ (string) $filter === (string) $value ? 'bg-teal-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.inventory.index') }}" class="mb-5 flex gap-2">
        @if ($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
        <input name="q" type="text" value="{{ $search }}" placeholder="{{ __('Search by item name') }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">{{ __('Search') }}</button>
    </form>

    @forelse ($items as $item)
        @php $state = $item->stockState(); @endphp
        <div class="mb-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 {{ $state !== 'ok' ? 'ring-2 ring-amber-300' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-slate-800">{{ $item->name }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $stateMeta[$state]['class'] }}">{{ $stateMeta[$state]['label'] }}</span>
                        @unless ($item->is_active)<span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600">{{ __('Inactive') }}</span>@endunless
                    </div>
                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                        <span class="font-semibold">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} {{ $units[$item->unit] ?? $item->unit }}</span>
                        <span class="text-xs text-slate-400">{{ __('Alert at') }}: {{ rtrim(rtrim(number_format((float) $item->min_quantity, 2), '0'), '.') }}</span>
                        @if (!is_null($item->cost_price))<span class="text-xs text-slate-400">{{ __('Purchase price') }}: {{ number_format((float) $item->cost_price, 2) }}</span>@endif
                    </div>
                </div>
                <div class="flex shrink-0 flex-col gap-2">
                    <a href="{{ route('admin.inventory.show', $item) }}" class="rounded-lg bg-teal-600 px-3 py-1.5 text-center text-sm font-medium text-white hover:bg-teal-700">{{ __('View') }}</a>
                    <a href="{{ route('admin.inventory.edit', $item) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Edit') }}</a>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white p-8 text-center text-slate-500 ring-1 ring-slate-200">{{ __('No items found.') }}</div>
    @endforelse

    <div class="mt-4">{{ $items->links() }}</div>
@endsection
