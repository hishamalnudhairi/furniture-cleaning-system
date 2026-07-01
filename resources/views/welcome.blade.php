<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Furniture Cleaning System') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">

        {{-- مبدّل اللغة --}}
        <div class="mb-8 flex items-center gap-2 text-sm">
            <span class="text-slate-500">{{ __('Language') }}:</span>
            <a href="{{ route('lang.switch', 'ar') }}"
               class="rounded-md px-3 py-1 transition {{ app()->getLocale() === 'ar' ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100' }}">
                {{ __('Arabic') }}
            </a>
            <a href="{{ route('lang.switch', 'en') }}"
               class="rounded-md px-3 py-1 transition {{ app()->getLocale() === 'en' ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100' }}">
                {{ __('English') }}
            </a>
        </div>

        {{-- البطاقة الرئيسية --}}
        <div class="w-full max-w-xl rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-brand-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-brand-600" fill="none"
                     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>

            <h1 class="mb-2 text-2xl font-bold text-slate-900">
                {{ __('Furniture Cleaning System') }}
            </h1>

            <p class="mb-1 text-lg font-medium text-brand-700">
                {{ __('The system is working successfully') }}
            </p>

            <p class="mb-6 text-sm leading-6 text-slate-500">
                {{ __('This is a temporary start page to confirm that Laravel and Tailwind are running correctly.') }}
            </p>

            <div class="rounded-lg bg-slate-50 p-4 text-sm ring-1 ring-slate-100">
                <p class="font-semibold text-slate-700">{{ __('Phase 1 completed') }}</p>
                <p class="mt-1 text-slate-500">{{ __('Laravel + Tailwind + Multilingual base are ready.') }}</p>
            </div>
        </div>

        <p class="mt-6 text-xs text-slate-400">Laravel {{ app()->version() }}</p>
    </div>
</body>
</html>
