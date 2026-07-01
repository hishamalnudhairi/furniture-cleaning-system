@extends('layouts.app')

@section('title', $item->name)

@section('content')
    @php
        $units = ['liter' => __('Liter'), 'bottle' => __('Bottle'), 'piece' => __('Piece'), 'pack' => __('Pack'), 'kg' => __('Kg'), 'other' => __('Other')];
        $stateMeta = [
            'ok' => ['label' => __('In stock'), 'class' => 'bg-emerald-100 text-emerald-800'],
            'low' => ['label' => __('Low'), 'class' => 'bg-amber-100 text-amber-800'],
            'out' => ['label' => __('Out of stock'), 'class' => 'bg-rose-100 text-rose-700'],
        ];
        $moveMeta = [
            'in' => ['label' => __('Added'), 'class' => 'text-emerald-700'],
            'out' => ['label' => __('Dispensed'), 'class' => 'text-rose-600'],
            'adjustment' => ['label' => __('Adjustment'), 'class' => 'text-sky-700'],
        ];
        $state = $item->stockState();
        $unitLabel = $units[$item->unit] ?? $item->unit;
    @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('admin.inventory.index') }}" class="text-sm text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back to list') }}</a>
        <a href="{{ route('admin.inventory.edit', $item) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Edit') }}</a>
    </div>

    <div class="mb-4 flex items-center gap-3">
        <h1 class="text-2xl font-bold text-slate-900">{{ $item->name }}</h1>
        <span class="rounded-full px-3 py-1 text-sm font-medium {{ $stateMeta[$state]['class'] }}">{{ $stateMeta[$state]['label'] }}</span>
    </div>

    @include('partials.flash')

    {{-- ملخص --}}
    <section class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200">
            <p class="text-xs text-slate-400">{{ __('Current quantity') }}</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} <span class="text-sm font-normal text-slate-400">{{ $unitLabel }}</span></p>
        </div>
        <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200">
            <p class="text-xs text-slate-400">{{ __('Alert quantity') }}</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ rtrim(rtrim(number_format((float) $item->min_quantity, 2), '0'), '.') }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200">
            <p class="text-xs text-slate-400">{{ __('Purchase price') }}</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ is_null($item->cost_price) ? '—' : number_format((float) $item->cost_price, 2) }}</p>
        </div>
    </section>

    {{-- إضافة / صرف --}}
    <div class="mb-4 grid gap-3 sm:grid-cols-2">
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-emerald-200">
            <h2 class="mb-3 text-base font-bold text-emerald-700">➕ {{ __('Add quantity') }}</h2>
            <form method="POST" action="{{ route('admin.inventory.add', $item) }}" class="space-y-3">
                @csrf
                <input name="quantity" type="number" min="0.01" step="0.01" placeholder="{{ __('Quantity') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="movement_date" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="notes" type="text" placeholder="{{ __('Notes') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">{{ __('Add quantity') }}</button>
            </form>
        </section>

        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-rose-200">
            <h2 class="mb-3 text-base font-bold text-rose-700">➖ {{ __('Dispense quantity') }}</h2>
            <form method="POST" action="{{ route('admin.inventory.dispense', $item) }}" class="space-y-3">
                @csrf
                <input name="quantity" type="number" min="0.01" step="0.01" placeholder="{{ __('Quantity') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="movement_date" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="notes" type="text" placeholder="{{ __('Notes') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button class="w-full rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">{{ __('Dispense quantity') }}</button>
            </form>
            @error('quantity')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </section>
    </div>

    {{-- تعديل يدوي (للمدير فقط وعند تفعيله في الإعدادات) --}}
    @if (auth()->user()->isAdmin() && ($settings->allow_manual_inventory_adjustment ?? true))
        <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-sky-200">
            <h2 class="mb-3 text-base font-bold text-sky-700">✎ {{ __('Manual adjustment') }}</h2>
            <form method="POST" action="{{ route('admin.inventory.adjust', $item) }}" class="grid gap-3 sm:grid-cols-3">
                @csrf
                <input name="quantity" type="number" min="0" step="0.01" placeholder="{{ __('New quantity') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="movement_date" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="notes" type="text" placeholder="{{ __('Notes') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <div class="sm:col-span-3">
                    <button class="w-full rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">{{ __('Apply adjustment') }}</button>
                </div>
            </form>
        </section>
    @endif

    {{-- آخر الحركات --}}
    <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Recent movements') }}</h2>
        @forelse ($item->movements as $movement)
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0">
                <div>
                    <span class="font-medium {{ $moveMeta[$movement->type]['class'] ?? 'text-slate-700' }}">{{ $moveMeta[$movement->type]['label'] ?? $movement->type }}</span>
                    <span class="text-slate-600">{{ rtrim(rtrim(number_format((float) $movement->quantity, 2), '0'), '.') }}</span>
                    @if ($movement->notes)<span class="text-xs text-slate-400">· {{ $movement->notes }}</span>@endif
                </div>
                <div class="text-end text-xs text-slate-400">
                    <span>{{ optional($movement->movement_date)->format('Y-m-d') ?? $movement->created_at->format('Y-m-d') }}</span>
                    <span class="block">{{ __('Balance') }}: {{ rtrim(rtrim(number_format((float) $movement->balance_after, 2), '0'), '.') }}</span>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400">{{ __('No movements yet.') }}</p>
        @endforelse
    </section>
@endsection
