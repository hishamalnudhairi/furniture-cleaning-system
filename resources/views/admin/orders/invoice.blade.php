<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Invoice') }} {{ $order->order_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            @page { size: A4; margin: 12mm; }
            body { background: #fff; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
    @php
        $locale = app()->getLocale();
        $bizName = $settings->shopName();
        $sr = $order->serviceRequest;
        $taxLabel = $settings->tax_name ?: __('VAT');
        $footer = $locale === 'ar'
            ? ($settings->invoice_footer_ar ?: $settings->invoice_footer_text)
            : ($settings->invoice_footer_en ?: $settings->invoice_footer_text);
        $statusLabels = ['new' => __('New'), 'cleaning' => __('Cleaning'), 'ready' => __('Ready for delivery'), 'delivered' => __('Delivered'), 'cancelled' => __('Cancelled')];
        $payLabels = ['unpaid' => __('Unpaid'), 'partial' => __('Partial'), 'paid' => __('Paid')];
    @endphp

    {{-- أزرار التحكم (لا تُطبع) --}}
    <div class="no-print mx-auto flex max-w-3xl items-center justify-between gap-2 px-4 py-4">
        <a href="{{ route('admin.orders.show', $order) }}" class="text-sm text-slate-600 hover:text-slate-900">← {{ __('Back') }}</a>
        <button onclick="window.print()" class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700">🖨️ {{ __('Print') }}</button>
    </div>

    <div class="mx-auto max-w-3xl bg-white p-8 shadow-sm print:max-w-full print:p-0 print:shadow-none">

        {{-- ترويسة المحل --}}
        <div class="mb-6 flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
            <div class="flex items-center gap-3">
                @if (($settings->invoice_show_logo ?? true) && $settings->logo_path)
                    <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="" class="h-16 w-16 rounded object-cover">
                @endif
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $bizName }}</h1>
                    @if ($settings->activity_number)<p class="text-xs text-slate-500">{{ __('Commercial registration') }}: {{ $settings->activity_number }}</p>@endif
                    @if ($taxEnabled && $settings->vatNumber())<p class="text-xs text-slate-500">{{ __('VAT number') }}: {{ $settings->vatNumber() }}</p>@endif
                </div>
            </div>
            <div class="text-end text-xs text-slate-500">
                @if ($settings->business_phone)<p>📞 {{ $settings->business_phone }}</p>@endif
                @if ($settings->business_email)<p>✉️ {{ $settings->business_email }}</p>@endif
                @if ($settings->business_address)<p>{{ $settings->business_address }}</p>@endif
            </div>
        </div>

        <h2 class="mb-4 text-center text-lg font-bold tracking-wide text-teal-700">{{ __('Invoice') }}</h2>

        {{-- معلومات الطلب والعميل --}}
        <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="mb-1 font-semibold text-slate-700">{{ __('Order') }}</p>
                <p>{{ __('Order number') }}: <span class="font-bold text-teal-700">{{ $order->order_number }}</span></p>
                <p>{{ __('Order date') }}: {{ $order->created_at->format('Y-m-d') }}</p>
                <p>{{ __('Order status') }}: {{ $statusLabels[$order->status] ?? $order->status }}</p>
                <p>{{ __('Payment status') }}: {{ $payLabels[$order->payment_status] ?? $order->payment_status }}</p>
            </div>
            <div>
                <p class="mb-1 font-semibold text-slate-700">{{ __('Customer') }}</p>
                <p>{{ $order->customer?->name }}</p>
                <p>{{ $order->customer?->phone }}</p>
                @if ($sr)<p>{{ $sr->wilaya }}@if ($sr->area) — {{ $sr->area }}@endif</p>@endif
                <p>{{ $sr->address ?? $order->customer?->address }}</p>
            </div>
        </div>

        {{-- جدول الخدمات --}}
        <table class="mb-4 w-full text-sm">
            <thead>
                <tr class="border-b-2 border-slate-300 text-start">
                    <th class="py-2 text-start">{{ __('Service') }}</th>
                    <th class="py-2 text-center">{{ __('Quantity') }}</th>
                    <th class="py-2 text-end">{{ __('Unit price') }}</th>
                    <th class="py-2 text-end">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-b border-slate-100">
                        <td class="py-2">{{ $item->description }}</td>
                        <td class="py-2 text-center">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td class="py-2 text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="py-2 text-end">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- الملخص المالي + QR --}}
        <div class="flex items-start justify-between gap-6">
            {{-- QR --}}
            <div class="text-center">
                @if ($qrSvg)
                    <div class="inline-block rounded border border-slate-200 p-2">{!! $qrSvg !!}</div>
                    <p class="mt-1 max-w-[180px] text-xs text-slate-500">{{ __('Scan QR Code for Customer Location') }}</p>
                @else
                    <p class="text-xs text-slate-400">{{ __('No location registered for the customer.') }}</p>
                @endif
            </div>

            {{-- المبالغ --}}
            <div class="w-64 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span>{{ number_format((float) $order->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Discount') }}</span><span>{{ number_format((float) $order->discount, 2) }}</span></div>
                @if ($taxEnabled && $taxAmount > 0)
                    <div class="flex justify-between text-xs text-slate-500"><span>{{ $taxLabel }} ({{ rtrim(rtrim(number_format($taxRate, 2), '0'), '.') }}%) — {{ __('included') }}</span><span>{{ number_format($taxAmount, 2) }}</span></div>
                @endif
                <div class="flex justify-between border-t border-slate-300 pt-1 text-base font-bold"><span>{{ __('Total') }}</span><span class="text-teal-700">{{ number_format((float) $order->total, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Paid') }}</span><span>{{ number_format((float) $order->paid_amount, 2) }}</span></div>
                <div class="flex justify-between font-semibold"><span class="text-slate-500">{{ __('Remaining') }}</span><span class="{{ (float) $order->due_amount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format((float) $order->due_amount, 2) }}</span></div>
            </div>
        </div>

        @if ($order->notes)
            <div class="mt-6 rounded bg-slate-50 p-3 text-sm">
                <span class="font-semibold">{{ __('Order notes') }}:</span> {{ $order->notes }}
            </div>
        @endif

        @if ($footer)
            <p class="mt-8 border-t border-slate-200 pt-4 text-center text-xs text-slate-500">{{ $footer }}</p>
        @endif
    </div>
</body>
</html>
