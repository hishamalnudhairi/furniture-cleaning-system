@extends('layouts.app')

@section('title', __('Reports'))

@section('content')
    <h1 class="mb-4 text-2xl font-bold text-slate-900">{{ __('Reports') }}</h1>

    {{-- بطاقات الملخص --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-report-stat :label="__('Today sales')" :value="number_format($todaySales, 2)" color="teal" />
        <x-report-stat :label="__('Today orders')" :value="$todayOrders" color="slate" />
        <x-report-stat :label="__('Paid today')" :value="number_format($todayPaid, 2)" color="emerald" />
        <x-report-stat :label="__('Customer dues')" :value="number_format($customerDues, 2)" color="rose" />
        <x-report-stat :label="__('Cleaning')" :value="$cleaningCount" color="sky" />
        <x-report-stat :label="__('Ready for delivery')" :value="$readyCount" color="emerald" />
        <x-report-stat :label="__('Driver dues')" :value="number_format($driverDues, 2)" color="amber" />
        <x-report-stat :label="__('Low / out of stock')" :value="$lowStockCount" color="rose" />
    </div>

    {{-- روابط التقارير التفصيلية --}}
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-400">{{ __('Detailed reports') }}</h2>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @foreach ([
            ['route' => 'admin.reports.sales', 'icon' => '💰', 'label' => __('Sales report')],
            ['route' => 'admin.reports.orders-status', 'icon' => '📊', 'label' => __('Orders by status')],
            ['route' => 'admin.reports.customer-dues', 'icon' => '🧾', 'label' => __('Customer dues report')],
            ['route' => 'admin.reports.drivers', 'icon' => '🚚', 'label' => __('Drivers report')],
            ['route' => 'admin.reports.inventory-low', 'icon' => '📦', 'label' => __('Low stock report')],
        ] as $r)
            <a href="{{ route($r['route']) }}" class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition hover:ring-brand-400">
                <span class="text-2xl">{{ $r['icon'] }}</span>
                <span class="font-medium text-slate-700">{{ $r['label'] }}</span>
            </a>
        @endforeach
    </div>
@endsection
