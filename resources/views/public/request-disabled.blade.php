@extends('layouts.public')

@section('title', __('Service unavailable'))

@section('content')
    @php
        $locale = app()->getLocale();
        $msg = ($locale === 'ar' ? $settings->out_of_hours_message_ar : $settings->out_of_hours_message_en);
    @endphp
    <div class="mx-auto mt-10 max-w-md rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-3xl">🚫</div>
        <h1 class="mb-2 text-xl font-bold text-slate-900">{{ $settings->shopName() }}</h1>
        <p class="text-sm leading-6 text-slate-600">
            {{ $msg ?: __('Online requests are currently unavailable. Please contact us directly.') }}
        </p>
        @if ($settings->show_shop_contact_on_public_page && $settings->business_phone)
            <p class="mt-3 text-sm font-medium text-brand-700">📞 {{ $settings->business_phone }}</p>
        @endif
    </div>
@endsection
