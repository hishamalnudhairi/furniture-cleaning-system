<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Request a Service')) — {{ __('Furniture Cleaning System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    {{-- مبدّل اللغة (تُخفى الإنجليزية إذا عُطّلت من الإعدادات) --}}
    @php $englishEnabled = \App\Models\BusinessSetting::current()->english_enabled; @endphp
    <div class="flex justify-end gap-1 px-4 py-3 text-sm">
        <a href="{{ route('lang.switch', 'ar') }}"
           class="rounded px-3 py-1 {{ app()->getLocale() === 'ar' ? 'bg-teal-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100' }}">العربية</a>
        @if ($englishEnabled)
            <a href="{{ route('lang.switch', 'en') }}"
               class="rounded px-3 py-1 {{ app()->getLocale() === 'en' ? 'bg-teal-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100' }}">English</a>
        @endif
    </div>

    <main class="mx-auto max-w-2xl px-4 pb-16">
        @yield('content')
    </main>

</body>
</html>
