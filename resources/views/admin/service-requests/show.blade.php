@extends('layouts.app')

@section('title', __('Request details'))

@section('content')
    @php
        $locale = app()->getLocale();
        $statusMeta = [
            'pending_review' => ['label' => __('Pending review'), 'class' => 'bg-amber-100 text-amber-800'],
            'contacted' => ['label' => __('Contacted'), 'class' => 'bg-sky-100 text-sky-800'],
            'confirmed' => ['label' => __('Confirmed'), 'class' => 'bg-brand-100 text-brand-800'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-rose-100 text-rose-700'],
        ];
        $methods = [
            'cleaning_at_customer_location' => __('Cleaning at customer location'),
            'pickup_from_customer' => __('Pickup from customer'),
            'customer_will_bring_items' => __('Customer will bring items'),
            'delivery_after_completion' => __('Delivery after completion'),
        ];
        $periods = ['morning' => __('Morning'), 'afternoon' => __('Afternoon'), 'evening' => __('Evening')];
        $mapUrl = $request->location_url ?: ($request->latitude && $request->longitude
            ? 'https://www.google.com/maps?q='.$request->latitude.','.$request->longitude
            : null);
    @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('admin.service-requests.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
            <span class="ib-flip">←</span> {{ __('Back to list') }}
        </a>
        <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusMeta[$request->status]['class'] ?? 'bg-slate-100 text-slate-600' }}">
            {{ $statusMeta[$request->status]['label'] ?? $request->status }}
        </span>
    </div>

    <h1 class="mb-4 text-2xl font-bold text-brand-700">{{ $request->request_number }}</h1>

    @include('admin.service-requests._flash')

    {{-- بيانات العميل --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Customer') }}</h2>
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-400">{{ __('Full name') }}</dt><dd class="font-medium text-slate-800">{{ $request->customer_name }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Phone number') }}</dt><dd class="font-medium text-slate-800" dir="ltr">{{ $request->customer_phone }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Customer type') }}</dt><dd class="text-slate-800">{{ $request->customer_type ? __(ucfirst($request->customer_type)) : '—' }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Wilaya') }}</dt><dd class="text-slate-800">{{ $request->wilaya ?: '—' }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Area / Village') }}</dt><dd class="text-slate-800">{{ $request->area ?: '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-400">{{ __('Detailed address') }}</dt><dd class="text-slate-800">{{ $request->address ?: '—' }}</dd></div>
        </dl>
    </section>

    {{-- الخدمات --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Requested services') }}</h2>
        @if (!empty($request->services_json))
            <ul class="space-y-2 text-sm">
                @foreach ($request->services_json as $item)
                    <li class="rounded-lg bg-slate-50 p-3">
                        @if (($item['name'] ?? null) === 'other')
                            <span class="font-medium text-slate-800">{{ __('Other service') }}</span>
                            @if (!empty($item['description']))<p class="text-slate-600">{{ $item['description'] }}</p>@endif
                        @else
                            <span class="font-medium text-slate-800">{{ $locale === 'ar' ? ($item['name_ar'] ?? '') : ($item['name_en'] ?? $item['name_ar'] ?? '') }}</span>
                            <div class="mt-1 flex flex-wrap gap-x-4 text-xs text-slate-500">
                                <span>{{ __('Quantity') }}: {{ $item['quantity'] ?? 1 }}</span>
                                <span>{{ __('Size') }}: {{ __(ucfirst($item['size'] ?? 'unknown')) }}</span>
                                @if (!empty($item['notes']))<span>{{ __('Notes') }}: {{ $item['notes'] }}</span>@endif
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-slate-400">—</p>
        @endif
    </section>

    {{-- التنفيذ والموعد --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Service method & timing') }}</h2>
        <dl class="grid gap-3 text-sm sm:grid-cols-3">
            <div><dt class="text-slate-400">{{ __('Service method') }}</dt><dd class="text-slate-800">{{ $methods[$request->service_method] ?? '—' }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Preferred date') }}</dt><dd class="text-slate-800">{{ $request->preferred_date?->format('Y-m-d') ?: '—' }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Preferred period') }}</dt><dd class="text-slate-800">{{ $periods[$request->preferred_period] ?? '—' }}</dd></div>
        </dl>
    </section>

    {{-- الموقع --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Map location') }}</h2>
        @if ($request->latitude && $request->longitude)
            <p class="text-sm text-slate-600" dir="ltr">{{ $request->latitude }}, {{ $request->longitude }}</p>
        @else
            <p class="text-sm text-slate-400">{{ __('No coordinates provided.') }}</p>
        @endif
        @if ($request->location_notes)<p class="mt-1 text-xs text-slate-500">{{ $request->location_notes }}</p>@endif
        @if ($mapUrl)
            <a href="{{ $mapUrl }}" target="_blank" rel="noopener"
               class="mt-3 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                📍 {{ __('Open in Google Maps') }}
            </a>
        @endif
    </section>

    {{-- الصور --}}
    @if ($request->images->isNotEmpty())
        <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Photos') }}</h2>
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                @foreach ($request->images as $image)
                    <a href="{{ asset('storage/'.$image->path) }}" target="_blank" rel="noopener">
                        <img src="{{ asset('storage/'.$image->path) }}" alt="" class="h-24 w-full rounded-lg object-cover ring-1 ring-slate-200">
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- الملاحظات --}}
    @if ($request->notes || $request->description)
        <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Notes') }}</h2>
            @if ($request->description)<p class="text-sm text-slate-700">{{ $request->description }}</p>@endif
            @if ($request->notes)<p class="mt-1 text-sm text-slate-600">{{ $request->notes }}</p>@endif
        </section>
    @endif

    {{-- الإجراءات --}}
    <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Actions') }}</h2>

        @if ($request->isConverted())
            <p class="text-sm text-brand-700">{{ __('This request has already been converted into an official order.') }}</p>
        @endif

        <div class="flex flex-wrap gap-2">
            @if ($request->status === 'pending_review')
                <form method="POST" action="{{ route('admin.service-requests.contacted', $request) }}">
                    @csrf
                    <button class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">{{ __('Mark as contacted') }}</button>
                </form>
            @endif

            @if ($request->canBeConverted())
                <a href="{{ route('admin.service-requests.convert.form', $request) }}"
                   class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                    {{ __('Convert to official order') }}
                </a>
            @endif

            @if (!$request->isCancelled() && !$request->isConverted())
                <form method="POST" action="{{ route('admin.service-requests.cancel', $request) }}"
                      onsubmit="return confirm('{{ __('Are you sure you want to cancel this request?') }}')">
                    @csrf
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">{{ __('Cancel request') }}</button>
                </form>
            @endif
        </div>
    </section>
@endsection
