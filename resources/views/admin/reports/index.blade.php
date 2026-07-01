@extends('layouts.app')

@section('title', __('Reports'))

@section('content')
    <h1 class="mb-1 text-2xl font-bold text-slate-900">{{ __('Reports') }}</h1>
    <p class="mb-6 text-sm text-slate-500">{{ __('Overview') }}</p>

    {{-- ===== المؤشرات المالية ===== --}}
    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-500">
        <span class="text-base">💰</span>{{ __('Financial') }}
    </h2>
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-report-stat :label="__('Today sales')" :value="number_format($todaySales, 2)" color="teal" icon="📈" />
        <x-report-stat :label="__('Paid today')" :value="number_format($todayPaid, 2)" color="emerald" icon="✅" />
        <x-report-stat :label="__('Customer dues')" :value="number_format($customerDues, 2)" color="rose" icon="🧾" />
        <x-report-stat :label="__('Driver dues')" :value="number_format($driverDues, 2)" color="amber" icon="🚚" />
    </div>

    {{-- ===== المؤشرات التشغيلية ===== --}}
    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-500">
        <span class="text-base">📊</span>{{ __('Operations') }}
    </h2>
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-report-stat :label="__('Today orders')" :value="$todayOrders" color="slate" icon="🧾" />
        <x-report-stat :label="__('Cleaning')" :value="$cleaningCount" color="sky" icon="🫧" />
        <x-report-stat :label="__('Ready for delivery')" :value="$readyCount" color="emerald" icon="📦" />
        <x-report-stat :label="__('Low / out of stock')" :value="$lowStockCount" color="rose" icon="⚠️" />
    </div>

    {{-- ===== روابط التقارير التفصيلية ===== --}}
    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-500">
        <span class="text-base">📁</span>{{ __('Detailed reports') }}
    </h2>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @foreach ([
            ['route' => 'admin.reports.sales', 'icon' => '💰', 'label' => __('Sales report')],
            ['route' => 'admin.reports.orders-status', 'icon' => '📊', 'label' => __('Orders by status')],
            ['route' => 'admin.reports.customer-dues', 'icon' => '🧾', 'label' => __('Customer dues report')],
            ['route' => 'admin.reports.drivers', 'icon' => '🚚', 'label' => __('Drivers report')],
            ['route' => 'admin.reports.inventory-low', 'icon' => '📦', 'label' => __('Low stock report')],
        ] as $r)
            <a href="{{ route($r['route']) }}" class="group flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md hover:ring-brand-400">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-xl transition group-hover:bg-brand-50">{{ $r['icon'] }}</span>
                <span class="flex-1 font-medium text-slate-700">{{ $r['label'] }}</span>
                <span class="text-slate-300 transition group-hover:text-brand-500"><span class="ib-flip">→</span></span>
            </a>
        @endforeach
    </div>
@endsection
