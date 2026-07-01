@extends('layouts.app')

@section('title', __('Deliveries'))

@section('content')
    @php
        $taskStatus = [
            'pending' => ['label' => __('Pending'), 'class' => 'bg-amber-100 text-amber-800'],
            'completed' => ['label' => __('Completed'), 'class' => 'bg-emerald-100 text-emerald-800'],
            'failed' => ['label' => __('Failed'), 'class' => 'bg-rose-100 text-rose-700'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-slate-200 text-slate-600'],
        ];
        $taskTypes = ['pickup' => __('Pickup'), 'delivery' => __('Delivery'), 'pickup_and_delivery' => __('Pickup & delivery')];
        $statusTabs = ['' => __('All'), 'pending' => __('Pending'), 'completed' => __('Completed'), 'failed' => __('Failed'), 'cancelled' => __('Cancelled')];
    @endphp

    <h1 class="mb-4 text-2xl font-bold text-slate-900">{{ __('Deliveries') }}</h1>

    @include('partials.flash')

    {{-- فلاتر الحالة --}}
    <div class="mb-3 flex flex-wrap gap-1.5">
        @foreach ($statusTabs as $value => $label)
            <a href="{{ route('admin.delivery-tasks.index', array_filter(['status' => $value, 'driver_id' => $driverId, 'date' => $date, 'q' => $search])) }}"
               class="rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ (string) $status === (string) $value ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- فلاتر السائق/التاريخ/البحث --}}
    <form method="GET" action="{{ route('admin.delivery-tasks.index') }}" class="mb-5 grid gap-2 sm:grid-cols-4">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <select name="driver_id" class="field">
            <option value="">{{ __('All drivers') }}</option>
            @foreach ($drivers as $d)
                <option value="{{ $d->id }}" @selected((string) $driverId === (string) $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
        <input name="date" type="date" value="{{ $date }}" class="field">
        <input name="q" type="search" value="{{ $search }}" placeholder="{{ __('Search by order no., name, or phone.') }}" class="field">
        <button type="submit" class="btn btn-dark">{{ __('Search') }}</button>
    </form>

    @forelse ($tasks as $task)
        <div class="mb-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.orders.show', $task->order_id) }}" class="font-bold text-brand-700 hover:underline">{{ $task->order?->order_number }}</a>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $taskTypes[$task->type] ?? $task->type }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $taskStatus[$task->status]['class'] ?? 'bg-slate-100' }}">{{ $taskStatus[$task->status]['label'] ?? $task->status }}</span>
                    </div>
                    <p class="mt-1 font-medium text-slate-800">{{ $task->order?->customer?->name }}</p>
                    <p class="text-sm text-slate-500" dir="ltr">{{ $task->order?->customer?->phone }}</p>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                        <span>{{ __('Driver') }}: {{ $task->driver?->name ?? '—' }}</span>
                        <span>{{ __('Customer fee') }}: {{ number_format((float) $task->customer_fee, 2) }}</span>
                        <span>{{ __('Driver due') }}: {{ number_format((float) $task->driver_fee, 2) }}</span>
                        @if ($task->scheduled_at)<span>📅 {{ $task->scheduled_at->format('Y-m-d') }}</span>@endif
                    </div>
                </div>
            </div>

            {{-- أزرار تحديث الحالة --}}
            @if ($task->status === 'pending')
                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach (['completed' => ['label' => __('Done'), 'class' => 'bg-emerald-600 hover:bg-emerald-700'], 'failed' => ['label' => __('Failed'), 'class' => 'bg-amber-600 hover:bg-amber-700'], 'cancelled' => ['label' => __('Cancel'), 'class' => 'bg-rose-600 hover:bg-rose-700']] as $value => $meta)
                        <form method="POST" action="{{ route('admin.delivery-tasks.status', $task) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $value }}">
                            <button class="w-full rounded-lg px-3 py-2 text-sm font-semibold text-white {{ $meta['class'] }}">{{ $meta['label'] }}</button>
                        </form>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <x-empty icon="📍" :message="__('No delivery tasks found.')" />
    @endforelse

    <div class="mt-4">{{ $tasks->links() }}</div>
@endsection
