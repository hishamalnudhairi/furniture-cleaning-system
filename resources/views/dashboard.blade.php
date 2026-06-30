@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    @php
        $user = auth()->user();
        $settings = \App\Models\BusinessSetting::current();
        $roleLabels = [
            'admin' => __('Administrator'),
            'accountant' => __('Accountant'),
            'worker' => __('Worker'),
            'driver' => __('Driver'),
        ];

        // أزرار العمليات المتاحة للجميع (موظف + مدير)
        $staffLinks = [
            ['label' => __('Customer Requests'), 'icon' => '📥', 'route' => route('admin.service-requests.index')],
            ['label' => __('Orders'), 'icon' => '🧾', 'route' => route('admin.orders.index')],
            ['label' => __('Customers'), 'icon' => '👤', 'route' => route('admin.customers.index')],
            ['label' => __('Services'), 'icon' => '🧺', 'route' => route('admin.services.index')],
            ['label' => __('Drivers'), 'icon' => '🚚', 'route' => route('admin.drivers.index')],
            ['label' => __('Deliveries'), 'icon' => '📍', 'route' => route('admin.delivery-tasks.index')],
        ];

        // المخزون يظهر فقط عند تفعيله في الإعدادات
        if ($settings->inventory_enabled) {
            $staffLinks[] = ['label' => __('Inventory'), 'icon' => '📦', 'route' => route('admin.inventory.index')];
        }

        // أزرار للمدير فقط
        $adminLinks = [
            ['label' => __('Settings'), 'icon' => '⚙️', 'route' => route('admin.settings.edit')],
            ['label' => __('Reports'), 'icon' => '📊', 'route' => route('admin.reports.index')],
            ['label' => __('Users Management'), 'icon' => '👥'],
        ];
    @endphp

    {{-- ترحيب + بيانات المستخدم --}}
    <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h1 class="text-2xl font-bold text-slate-900">
            {{ __('Welcome, :name', ['name' => $user->name]) }}
        </h1>
        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
            <span class="text-slate-500">{{ __('Role') }}:</span>
            <span class="rounded-full bg-teal-100 px-3 py-0.5 font-medium text-teal-800">
                {{ $roleLabels[$user->role] ?? $user->role }}
            </span>
            <span class="text-slate-400">·</span>
            <span class="text-slate-500">{{ $user->email }}</span>
        </div>
    </div>

    {{-- مؤشرات حقيقية --}}
    @isset($metrics)
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-400">{{ __('Overview') }}</h2>
        <div class="mb-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <x-report-stat :label="__('Today sales')" :value="number_format($metrics['todaySales'], 2)" color="teal" />
            <x-report-stat :label="__('Today orders')" :value="$metrics['todayOrders']" color="slate" />
            <x-report-stat :label="__('Pending review')" :value="$metrics['pendingReview']" color="amber" />
            <x-report-stat :label="__('Cleaning')" :value="$metrics['cleaningCount']" color="sky" />
            <x-report-stat :label="__('Ready for delivery')" :value="$metrics['readyCount']" color="emerald" />
            <x-report-stat :label="__('Customer dues')" :value="number_format($metrics['customerDues'], 2)" color="rose" />
            <x-report-stat :label="__('Driver dues')" :value="number_format($metrics['driverDues'], 2)" color="amber" />
            <x-report-stat :label="__('Low / out of stock')" :value="$metrics['lowStockCount']" color="rose" />
        </div>
    @endisset

    {{-- شبكة الأزرار --}}
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-400">{{ __('Sections') }}</h2>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">

        @foreach ($staffLinks as $link)
            @if (!empty($link['route']))
                <a href="{{ $link['route'] }}"
                   class="flex flex-col items-center gap-2 rounded-xl bg-white p-5 text-center shadow-sm ring-1 ring-slate-200 transition hover:ring-teal-400">
                    <span class="text-2xl">{{ $link['icon'] }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ $link['label'] }}</span>
                    <span class="rounded-full bg-teal-50 px-2 py-0.5 text-[10px] text-teal-600">{{ __('Open') }}</span>
                </a>
            @else
                <div class="flex cursor-not-allowed flex-col items-center gap-2 rounded-xl bg-white p-5 text-center shadow-sm ring-1 ring-slate-200">
                    <span class="text-2xl">{{ $link['icon'] }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ $link['label'] }}</span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-400">{{ __('Coming soon') }}</span>
                </div>
            @endif
        @endforeach

        @if ($user->isAdmin())
            @foreach ($adminLinks as $link)
                @if (!empty($link['route']))
                    <a href="{{ $link['route'] }}"
                       class="flex flex-col items-center gap-2 rounded-xl bg-amber-50 p-5 text-center shadow-sm ring-1 ring-amber-200 transition hover:ring-amber-400">
                        <span class="text-2xl">{{ $link['icon'] }}</span>
                        <span class="text-sm font-medium text-slate-700">{{ $link['label'] }}</span>
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] text-amber-600">{{ __('Admin only') }}</span>
                    </a>
                @else
                    <div class="flex cursor-not-allowed flex-col items-center gap-2 rounded-xl bg-amber-50 p-5 text-center shadow-sm ring-1 ring-amber-200">
                        <span class="text-2xl">{{ $link['icon'] }}</span>
                        <span class="text-sm font-medium text-slate-700">{{ $link['label'] }}</span>
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] text-amber-600">{{ __('Admin only') }}</span>
                    </div>
                @endif
            @endforeach
        @endif

    </div>
@endsection
