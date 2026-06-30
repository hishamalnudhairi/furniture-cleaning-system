<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Receipt') }} {{ $order->order_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php $width = (int) ($settings->thermal_paper_width ?? 80); @endphp
    <style>
        .receipt { width: {{ $width }}mm; }
        @media print {
            .no-print { display: none !important; }
            @page { size: {{ $width }}mm auto; margin: 0; }
            body { background: #fff; margin: 0; }
            .receipt { width: {{ $width }}mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    @php
        $locale = app()->getLocale();
        $bizName = $settings->shopName();
        $footer = $locale === 'ar'
            ? ($settings->invoice_footer_ar ?: $settings->invoice_footer_text)
            : ($settings->invoice_footer_en ?: $settings->invoice_footer_text);
    @endphp

    <div class="no-print flex items-center justify-between gap-2 p-3">
        <a href="{{ route('admin.orders.show', $order) }}" class="text-sm text-slate-600 hover:text-slate-900">← {{ __('Back') }}</a>
        <button onclick="window.print()" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">🖨️ {{ __('Print') }}</button>
    </div>

    <div class="receipt mx-auto bg-white p-2 text-xs leading-5 text-black">
        {{-- اسم المحل --}}
        <div class="text-center">
            <p class="text-sm font-bold">{{ $bizName }}</p>
            @if ($settings->business_phone)<p>{{ $settings->business_phone }}</p>@endif
        </div>

        <div class="my-1 border-t border-dashed border-black"></div>

        <p>{{ __('Order number') }}: <span class="font-bold">{{ $order->order_number }}</span></p>
        <p>{{ __('Order date') }}: {{ $order->created_at->format('Y-m-d H:i') }}</p>
        <p>{{ __('Customer') }}: {{ $order->customer?->name }}</p>
        <p>{{ $order->customer?->phone }}</p>

        <div class="my-1 border-t border-dashed border-black"></div>

        {{-- الخدمات مختصرة --}}
        @foreach ($order->items as $item)
            <div class="flex justify-between gap-2">
                <span class="min-w-0 truncate">{{ $item->description }} ×{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</span>
                <span>{{ number_format((float) $item->line_total, 2) }}</span>
            </div>
        @endforeach

        <div class="my-1 border-t border-dashed border-black"></div>

        @if ($taxEnabled && $taxAmount > 0)
            <div class="flex justify-between text-[10px]"><span>{{ ($settings->tax_name ?: __('VAT')) }} — {{ __('included') }}</span><span>{{ number_format($taxAmount, 2) }}</span></div>
        @endif
        <div class="flex justify-between font-bold"><span>{{ __('Total') }}</span><span>{{ number_format((float) $order->total, 2) }}</span></div>
        <div class="flex justify-between"><span>{{ __('Paid') }}</span><span>{{ number_format((float) $order->paid_amount, 2) }}</span></div>
        <div class="flex justify-between font-bold"><span>{{ __('Remaining') }}</span><span>{{ number_format((float) $order->due_amount, 2) }}</span></div>

        {{-- QR --}}
        @if ($qrSvg)
            <div class="my-1 border-t border-dashed border-black"></div>
            <div class="flex flex-col items-center">
                <div style="width: 40mm;">{!! $qrSvg !!}</div>
                <p class="mt-1 text-center text-[10px]">{{ __('Scan QR Code for Customer Location') }}</p>
            </div>
        @endif

        <div class="my-1 border-t border-dashed border-black"></div>
        <p class="text-center">{{ __('Thank you for choosing us!') }}</p>
        @if ($footer)<p class="mt-1 text-center text-[10px] text-slate-600">{{ $footer }}</p>@endif
    </div>
</body>
</html>
