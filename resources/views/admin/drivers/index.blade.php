@extends('layouts.app')

@section('title', __('Drivers'))

@section('content')
    @php
        $payTypes = ['per_task' => __('Per task'), 'per_day' => __('Per day')];
    @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Drivers') }}</h1>
        <a href="{{ route('admin.drivers.create') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">+ {{ __('Add driver') }}</a>
    </div>

    @include('partials.flash')

    @forelse ($drivers as $driver)
        <div class="mb-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-slate-800">{{ $driver->name }}</span>
                        @if ($driver->is_active)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('Active') }}</span>
                        @else
                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600">{{ __('Inactive') }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500">{{ $driver->phone }}</p>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                        <span>{{ __('Payment type') }}: {{ $payTypes[$driver->payment_type] ?? $driver->payment_type }}</span>
                        <span>{{ __('Default delivery fee') }}: {{ number_format((float) $driver->default_delivery_fee, 2) }}</span>
                        <span>{{ __('Tasks') }}: {{ $driver->delivery_tasks_count }}</span>
                    </div>
                </div>
                <div class="flex shrink-0 flex-col gap-2">
                    <a href="{{ route('admin.drivers.show', $driver) }}" class="rounded-lg bg-teal-600 px-3 py-1.5 text-center text-sm font-medium text-white hover:bg-teal-700">{{ __('View') }}</a>
                    <a href="{{ route('admin.drivers.edit', $driver) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Edit') }}</a>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white p-8 text-center text-slate-500 ring-1 ring-slate-200">{{ __('No drivers found.') }}</div>
    @endforelse

    <div class="mt-4">{{ $drivers->links() }}</div>
@endsection
