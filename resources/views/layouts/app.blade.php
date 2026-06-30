<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Dashboard')) — {{ __('Furniture Cleaning System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    {{-- الشريط العلوي --}}
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3">
            @php $appSettings = \App\Models\BusinessSetting::current(); @endphp
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-teal-700">
                @if ($appSettings->logo_path)
                    <img src="{{ asset('storage/'.$appSettings->logo_path) }}" alt="" class="h-8 w-8 rounded-lg object-cover">
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white">✦</span>
                @endif
                <span class="hidden sm:inline">{{ $appSettings->shopName() }}</span>
            </a>

            <div class="flex items-center gap-3 text-sm">
                {{-- مبدّل اللغة --}}
                <div class="flex items-center gap-1">
                    <a href="{{ route('lang.switch', 'ar') }}"
                       class="rounded px-2 py-1 {{ app()->getLocale() === 'ar' ? 'bg-teal-600 text-white' : 'text-slate-500 hover:bg-slate-100' }}">ع</a>
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="rounded px-2 py-1 {{ app()->getLocale() === 'en' ? 'bg-teal-600 text-white' : 'text-slate-500 hover:bg-slate-100' }}">EN</a>
                </div>

                @auth
                    <span class="hidden text-slate-600 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-lg bg-slate-100 px-3 py-1.5 font-medium text-slate-700 transition hover:bg-rose-100 hover:text-rose-700">
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
