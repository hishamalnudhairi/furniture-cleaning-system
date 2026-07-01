<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Request a Service')) — {{ __('Furniture Cleaning System') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    {{-- مبدّل اللغة (تُخفى الإنجليزية إذا عُطّلت من الإعدادات) --}}
    @php $englishEnabled = \App\Models\BusinessSetting::current()->english_enabled; @endphp
    <div class="mx-auto flex max-w-2xl justify-end gap-1 px-4 py-3 text-sm">
        <a href="{{ route('lang.switch', 'ar') }}"
           class="flex h-9 items-center rounded-full px-4 font-medium transition {{ app()->getLocale() === 'ar' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100' }}">العربية</a>
        @if ($englishEnabled)
            <a href="{{ route('lang.switch', 'en') }}"
               class="flex h-9 items-center rounded-full px-4 font-medium transition {{ app()->getLocale() === 'en' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100' }}">English</a>
        @endif
    </div>

    <main class="mx-auto max-w-2xl px-4 pb-16">
        @yield('content')
    </main>

</body>
</html>
