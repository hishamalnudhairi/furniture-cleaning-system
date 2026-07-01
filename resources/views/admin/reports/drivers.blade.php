@extends('layouts.app')

@section('title', __('Drivers report'))

@section('content')
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back to reports') }}</a>
    <h1 class="mb-4 mt-2 text-2xl font-bold text-slate-900">{{ __('Drivers report') }}</h1>

    {{-- الفلتر --}}
    <form method="GET" action="{{ route('admin.reports.drivers') }}" class="mb-5 grid gap-2 sm:grid-cols-4">
        <div>
            <label class="mb-1 block text-xs text-slate-500">{{ __('From date') }}</label>
            <input name="start_date" type="date" value="{{ $start }}" class="field">
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500">{{ __('To date') }}</label>
            <input name="end_date" type="date" value="{{ $end }}" class="field">
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500">{{ __('Driver') }}</label>
            <select name="driver_id" class="field">
                <option value="">{{ __('All drivers') }}</option>
                @foreach ($allDrivers as $d)
                    <option value="{{ $d->id }}" @selected((string) $driverId === (string) $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">{{ __('Apply filter') }}</button>
        </div>
    </form>

    @forelse ($rows as $row)
        <div class="mb-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('admin.drivers.show', $row['driver']) }}" class="font-bold text-brand-700 hover:underline">{{ $row['driver']->name }}</a>
                <span class="text-sm font-bold {{ $row['remaining'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ __('Remaining for driver') }}: {{ number_format($row['remaining'], 2) }}</span>
            </div>
            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                <span>{{ __('Completed tasks') }}: {{ $row['completed'] }}</span>
                <span>{{ __('Total due') }}: {{ number_format($row['due'], 2) }}</span>
                <span>{{ __('Total paid') }}: {{ number_format($row['paid'], 2) }}</span>
                <span>{{ __('Failed / cancelled') }}: {{ $row['failed_cancelled'] }}</span>
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white p-8 text-center text-slate-500 ring-1 ring-slate-200">{{ __('No drivers found.') }}</div>
    @endforelse
@endsection
