@extends('layouts.app')

@section('title', __('Convert to official order'))

@section('content')
    @php
        $locale = app()->getLocale();
        // بناء الصفوف الأولية من المدخلات السابقة أو من خدمات الطلب المبدئي
        $rows = old('items');
        if (! $rows) {
            $rows = [];
            foreach ((array) ($request->services_json ?? []) as $item) {
                if (($item['name'] ?? null) === 'other') {
                    $desc = trim((string) ($item['description'] ?? ''));
                    $rows[] = ['description' => $desc !== '' ? $desc : __('Other service'), 'quantity' => 1, 'unit_price' => '', 'service_id' => ''];
                } else {
                    $name = $locale === 'ar' ? ($item['name_ar'] ?? '') : ($item['name_en'] ?? $item['name_ar'] ?? '');
                    $size = $item['size'] ?? 'unknown';
                    $desc = $name.($size && $size !== 'unknown' ? ' ('.__(ucfirst($size)).')' : '');
                    $rows[] = ['description' => $desc, 'quantity' => $item['quantity'] ?? 1, 'unit_price' => '', 'service_id' => $item['service_id'] ?? ''];
                }
            }
            if (empty($rows)) {
                $rows[] = ['description' => '', 'quantity' => 1, 'unit_price' => '', 'service_id' => ''];
            }
        }
    @endphp

    <a href="{{ route('admin.service-requests.show', $request) }}" class="text-sm text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back') }}</a>
    <h1 class="mb-1 mt-2 text-2xl font-bold text-slate-900">{{ __('Convert to official order') }}</h1>
    <p class="mb-4 text-sm text-slate-500">{{ __('Review and set the final prices. The customer never sees a final price until you confirm.') }}</p>

    @include('partials.form-errors')

    <form method="POST" action="{{ route('admin.service-requests.convert', $request) }}" class="space-y-5">
        @csrf

        {{-- بيانات العميل --}}
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-slate-900">{{ __('Customer') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Full name') }}</label>
                    <input name="customer_name" value="{{ old('customer_name', $request->customer_name) }}" required
                           class="field">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Phone number') }}</label>
                    <input name="phone" value="{{ old('phone', $request->customer_phone) }}" required
                           class="field">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Wilaya') }}</label>
                    <input name="wilaya" value="{{ old('wilaya', $request->wilaya) }}"
                           class="field">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Area / Village') }}</label>
                    <input name="area" value="{{ old('area', $request->area) }}"
                           class="field">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Detailed address') }}</label>
                    <input name="address" value="{{ old('address', $request->address) }}"
                           class="field">
                </div>
            </div>
        </section>

        {{-- الخدمات والأسعار --}}
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Services & prices') }}</h2>
            <div id="items-wrapper" class="space-y-3">
                @foreach ($rows as $i => $row)
                    <div class="item-row grid gap-2 sm:grid-cols-12">
                        <input type="hidden" name="items[{{ $i }}][service_id]" value="{{ $row['service_id'] ?? '' }}">
                        <div class="sm:col-span-6">
                            <label class="mb-1 block text-xs text-slate-500">{{ __('Service / description') }}</label>
                            <input name="items[{{ $i }}][description]" value="{{ $row['description'] ?? '' }}"
                                   class="field">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs text-slate-500">{{ __('Quantity') }}</label>
                            <input name="items[{{ $i }}][quantity]" type="number" min="1" step="1" value="{{ $row['quantity'] ?? 1 }}"
                                   class="item-qty field">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="mb-1 block text-xs text-slate-500">{{ __('Unit price') }}</label>
                            <input name="items[{{ $i }}][unit_price]" type="number" min="0" step="0.01" value="{{ $row['unit_price'] ?? '' }}"
                                   class="item-price field">
                        </div>
                        <div class="flex items-end sm:col-span-1">
                            <button type="button" class="remove-row w-full rounded-lg bg-rose-50 px-2 py-2 text-sm text-rose-600 hover:bg-rose-100">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-row" class="mt-3 rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                + {{ __('Add service') }}
            </button>
        </section>

        {{-- المالية --}}
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-slate-900">{{ __('Payment') }}</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Discount') }}</label>
                    <input id="discount" name="discount" type="number" min="0" step="0.01" value="{{ old('discount', 0) }}"
                           class="field">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Payment method') }}</label>
                    <select name="payment_method" class="field">
                        @foreach (['cash' => __('Cash'), 'card' => __('Card'), 'transfer' => __('Transfer'), 'later' => __('Pay later')] as $val => $label)
                            <option value="{{ $val }}" @selected(old('payment_method', 'cash') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Payment status') }}</label>
                    <select name="payment_status" class="field">
                        @foreach (['unpaid' => __('Unpaid'), 'partial' => __('Partial'), 'paid' => __('Paid')] as $val => $label)
                            <option value="{{ $val }}" @selected(old('payment_status', 'unpaid') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Calculated automatically from the paid amount.') }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Paid amount') }}</label>
                    <input id="paid_amount" name="paid_amount" type="number" min="0" step="0.01" value="{{ old('paid_amount', 0) }}"
                           class="field">
                </div>
            </div>

            {{-- ملخص الحساب --}}
            <div class="mt-4 space-y-1 rounded-lg bg-slate-50 p-4 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span id="sum-subtotal" class="font-medium">0.00</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Discount') }}</span><span id="sum-discount" class="font-medium">0.00</span></div>
                <div class="flex justify-between border-t border-slate-200 pt-1 text-base"><span class="font-semibold">{{ __('Total') }}</span><span id="sum-total" class="font-bold text-brand-700">0.00</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Paid') }}</span><span id="sum-paid" class="font-medium">0.00</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Remaining') }}</span><span id="sum-remaining" class="font-bold text-rose-600">0.00</span></div>
            </div>
        </section>

        {{-- ملاحظات داخلية --}}
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <label class="mb-1 block text-base font-bold text-slate-900">{{ __('Internal notes') }}</label>
            <textarea name="notes" rows="2" class="field">{{ old('notes') }}</textarea>
        </section>

        <button type="submit" class="btn btn-primary w-full text-base">
            {{ __('Create official order') }}
        </button>
    </form>

    {{-- قالب صف جديد --}}
    <template id="row-template">
        <div class="item-row grid gap-2 sm:grid-cols-12">
            <input type="hidden" name="items[__INDEX__][service_id]" value="">
            <div class="sm:col-span-6">
                <label class="mb-1 block text-xs text-slate-500">{{ __('Service / description') }}</label>
                <input name="items[__INDEX__][description]" class="field">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs text-slate-500">{{ __('Quantity') }}</label>
                <input name="items[__INDEX__][quantity]" type="number" min="1" step="1" value="1" class="item-qty field">
            </div>
            <div class="sm:col-span-3">
                <label class="mb-1 block text-xs text-slate-500">{{ __('Unit price') }}</label>
                <input name="items[__INDEX__][unit_price]" type="number" min="0" step="0.01" class="item-price field">
            </div>
            <div class="flex items-end sm:col-span-1">
                <button type="button" class="remove-row w-full rounded-lg bg-rose-50 px-2 py-2 text-sm text-rose-600 hover:bg-rose-100">✕</button>
            </div>
        </div>
    </template>

    <script>
        (function () {
            var wrapper = document.getElementById('items-wrapper');
            var tpl = document.getElementById('row-template').innerHTML;
            var index = {{ count($rows) }};

            function num(v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; }

            function recalc() {
                var subtotal = 0;
                wrapper.querySelectorAll('.item-row').forEach(function (row) {
                    var q = num(row.querySelector('.item-qty')?.value);
                    var p = num(row.querySelector('.item-price')?.value);
                    subtotal += q * p;
                });
                var discount = num(document.getElementById('discount').value);
                if (discount > subtotal) discount = subtotal;
                var total = subtotal - discount;
                var paid = num(document.getElementById('paid_amount').value);
                if (paid > total) paid = total;
                var remaining = total - paid;

                document.getElementById('sum-subtotal').textContent = subtotal.toFixed(2);
                document.getElementById('sum-discount').textContent = discount.toFixed(2);
                document.getElementById('sum-total').textContent = total.toFixed(2);
                document.getElementById('sum-paid').textContent = paid.toFixed(2);
                document.getElementById('sum-remaining').textContent = remaining.toFixed(2);
            }

            document.getElementById('add-row').addEventListener('click', function () {
                var html = tpl.replace(/__INDEX__/g, index++);
                var div = document.createElement('div');
                div.innerHTML = html.trim();
                wrapper.appendChild(div.firstChild);
                recalc();
            });

            wrapper.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-row')) {
                    var rows = wrapper.querySelectorAll('.item-row');
                    if (rows.length > 1) {
                        e.target.closest('.item-row').remove();
                        recalc();
                    }
                }
            });

            document.addEventListener('input', recalc);
            recalc();
        })();
    </script>
@endsection
