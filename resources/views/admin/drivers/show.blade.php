@extends('layouts.app')

@section('title', $driver->name)

@section('content')
    @php
        $payTypes = ['per_task' => __('Per task'), 'per_day' => __('Per day')];
        $taskStatus = [
            'pending' => ['label' => __('Pending'), 'class' => 'bg-amber-100 text-amber-800'],
            'completed' => ['label' => __('Completed'), 'class' => 'bg-emerald-100 text-emerald-800'],
            'failed' => ['label' => __('Failed'), 'class' => 'bg-rose-100 text-rose-700'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-slate-200 text-slate-600'],
        ];
        $taskTypes = ['pickup' => __('Pickup'), 'delivery' => __('Delivery'), 'pickup_and_delivery' => __('Pickup & delivery')];
    @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('admin.drivers.index') }}" class="text-sm text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back to list') }}</a>
        <a href="{{ route('admin.drivers.edit', $driver) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Edit') }}</a>
    </div>

    <h1 class="mb-4 text-2xl font-bold text-slate-900">{{ $driver->name }}</h1>

    @include('partials.flash')

    {{-- بيانات السائق --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-400">{{ __('Phone number') }}</dt><dd class="font-medium text-slate-800">{{ $driver->phone }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Payment type') }}</dt><dd class="text-slate-800">{{ $payTypes[$driver->payment_type] ?? $driver->payment_type }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Default delivery fee') }}</dt><dd class="text-slate-800">{{ number_format((float) $driver->default_delivery_fee, 2) }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Status') }}</dt><dd>{{ $driver->is_active ? __('Active') : __('Inactive') }}</dd></div>
            @if ($driver->notes)<div class="sm:col-span-2"><dt class="text-slate-400">{{ __('Notes') }}</dt><dd class="text-slate-700">{{ $driver->notes }}</dd></div>@endif
        </dl>
    </section>

    {{-- ملخص المستحقات --}}
    <section class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200">
            <p class="text-xs text-slate-400">{{ __('Completed tasks') }}</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ $completedCount }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200">
            <p class="text-xs text-slate-400">{{ __('Total due') }}</p>
            <p class="mt-1 text-xl font-bold text-brand-700">{{ number_format($totalDue, 2) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200">
            <p class="text-xs text-slate-400">{{ __('Total paid') }}</p>
            <p class="mt-1 text-xl font-bold text-slate-700">{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200">
            <p class="text-xs text-slate-400">{{ __('Remaining for driver') }}</p>
            <p class="mt-1 text-xl font-bold {{ $remaining > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($remaining, 2) }}</p>
        </div>
    </section>

    {{-- تسجيل دفعة للسائق --}}
    @if ($remaining > 0)
        <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Record driver payment') }}</h2>
            <form method="POST" action="{{ route('admin.drivers.payments.store', $driver) }}" class="grid gap-3 sm:grid-cols-3">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Amount') }}</label>
                    <input name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Paid at') }}</label>
                    <input name="paid_at" type="date" value="{{ old('paid_at') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                    <input name="notes" type="text" value="{{ old('notes') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="sm:col-span-3">
                    <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Record payment') }}</button>
                </div>
            </form>
            @error('amount')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </section>
    @endif

    {{-- مهام السائق --}}
    <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Delivery tasks') }}</h2>
        @forelse ($driver->deliveryTasks->sortByDesc('created_at') as $task)
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0">
                <div class="min-w-0">
                    <a href="{{ route('admin.orders.show', $task->order_id) }}" class="font-medium text-brand-700 hover:underline">{{ $task->order?->order_number }}</a>
                    <span class="text-slate-400">· {{ $taskTypes[$task->type] ?? $task->type }}</span>
                    <p class="text-xs text-slate-500">{{ $task->order?->customer?->name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-600">{{ number_format((float) $task->driver_fee, 2) }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $taskStatus[$task->status]['class'] ?? 'bg-slate-100' }}">{{ $taskStatus[$task->status]['label'] ?? $task->status }}</span>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400">{{ __('No tasks yet.') }}</p>
        @endforelse
    </section>
@endsection
