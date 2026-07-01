<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Dashboard')) — {{ __('Furniture Cleaning System') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    {{-- الشريط العلوي --}}
    <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3">
            @php $appSettings = \App\Models\BusinessSetting::current(); @endphp
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-brand-700">
                @if ($appSettings->logo_path)
                    <img src="{{ asset('storage/'.$appSettings->logo_path) }}" alt="" class="h-9 w-9 rounded-xl object-cover">
                @else
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-white shadow-sm">✦</span>
                @endif
                <span class="hidden truncate sm:inline">{{ $appSettings->shopName() }}</span>
            </a>

            <div class="flex items-center gap-2 text-sm sm:gap-3">
                {{-- مبدّل اللغة --}}
                <div class="flex items-center gap-1 rounded-full bg-slate-100 p-0.5">
                    <a href="{{ route('lang.switch', 'ar') }}"
                       class="flex h-8 min-w-9 items-center justify-center rounded-full px-2 font-medium transition {{ app()->getLocale() === 'ar' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">ع</a>
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="flex h-8 min-w-9 items-center justify-center rounded-full px-2 font-medium transition {{ app()->getLocale() === 'en' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">EN</a>
                </div>

                @auth
                    <span class="hidden text-slate-600 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex h-9 items-center rounded-lg bg-slate-100 px-3 font-medium text-slate-700 transition hover:bg-rose-100 hover:text-rose-700">
                            {{ __('Logout') }}
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @yield('content')
    </main>

</body>
</html>
