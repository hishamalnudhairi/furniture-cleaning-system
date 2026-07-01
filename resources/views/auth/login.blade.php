<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Login') }} — {{ __('Furniture Cleaning System') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme')
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 to-brand-50 text-slate-800 antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">

            {{-- مبدّل اللغة --}}
            <div class="mb-4 flex justify-center gap-1 text-sm">
                <a href="{{ route('lang.switch', 'ar') }}"
                   class="rounded px-3 py-1 {{ app()->getLocale() === 'ar' ? 'bg-brand-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50' }}">العربية</a>
                <a href="{{ route('lang.switch', 'en') }}"
                   class="rounded px-3 py-1 {{ app()->getLocale() === 'en' ? 'bg-brand-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50' }}">English</a>
            </div>

            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
                <div class="mb-6 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-2xl text-white">✦</div>
                    <h1 class="text-xl font-bold text-slate-900">{{ __('Welcome back') }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Sign in to your account') }}</p>
                </div>

                {{-- أخطاء عامة --}}
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-rose-50 p-3 text-sm text-rose-700 ring-1 ring-rose-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               class="field"
                               placeholder="admin@example.com">
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Password') }}</label>
                        <input id="password" name="password" type="password" required
                               class="field"
                               placeholder="••••••••">
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        {{ __('Remember me') }}
                    </label>

                    <button type="submit" class="btn btn-primary w-full text-base shadow-sm">
                        {{ __('Login') }}
                    </button>
                </form>

                {{-- حسابات تجريبية --}}
                <div class="mt-6 rounded-lg bg-slate-50 p-3 text-xs text-slate-500 ring-1 ring-slate-100">
                    <p class="mb-1 font-semibold text-slate-600">{{ __('Demo accounts') }}:</p>
                    <p>admin@example.com / password</p>
                    <p>worker@example.com / password</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
