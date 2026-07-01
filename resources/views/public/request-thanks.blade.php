@extends('layouts.public')

@section('title', __('Request received'))

@section('content')
    <div class="mx-auto mt-10 max-w-md rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-brand-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-brand-600" fill="none"
                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
        </div>

        <h1 class="mb-2 text-xl font-bold text-slate-900">{{ __('Request received') }}</h1>

        <p class="mb-5 text-sm leading-6 text-slate-600">
            {{ ($successMessage ?? null) ?: __('Your request has been received successfully. We will contact you soon to confirm the details, price, and suitable appointment.') }}
        </p>

        <div class="mb-6 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-100">
            <p class="text-xs text-slate-500">{{ __('Your request number') }}</p>
            <p class="mt-1 text-2xl font-bold tracking-wider text-brand-700">{{ $requestNumber }}</p>
        </div>

        <a href="{{ route('request-service.create') }}"
           class="inline-block rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
            {{ __('Submit another request') }}
        </a>
    </div>
@endsection
