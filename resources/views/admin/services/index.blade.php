@extends('layouts.app')

@section('title', __('Services'))

@section('content')
    @php $locale = app()->getLocale(); @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Services') }}</h1>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.services.create') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">+ {{ __('Add service') }}</a>
        @endif
    </div>

    @include('partials.flash')

    @forelse ($services as $service)
        <div class="mb-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-slate-800">{{ $locale === 'ar' ? $service->name_ar : ($service->name_en ?: $service->name_ar) }}</span>
                        @if ($service->is_active)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('Active') }}</span>
                        @else
                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600">{{ __('Inactive') }}</span>
                        @endif
                    </div>
                    @if ($service->name_en && $locale === 'ar')<p class="text-xs text-slate-400">{{ $service->name_en }}</p>@endif
                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                        @if ($service->unit)<span>{{ __('Unit') }}: {{ $service->unit }}</span>@endif
                        <span>{{ __('Base price') }}: {{ is_null($service->default_price) ? '—' : number_format((float) $service->default_price, 2) }}</span>
                        <span>{{ __('Editable price') }}: {{ $service->is_price_editable ? __('Yes') : __('No') }}</span>
                    </div>
                </div>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.services.edit', $service) }}" class="shrink-0 rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Edit') }}</a>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white p-8 text-center text-slate-500 ring-1 ring-slate-200">{{ __('No services found.') }}</div>
    @endforelse

    <div class="mt-4">{{ $services->links() }}</div>
@endsection
