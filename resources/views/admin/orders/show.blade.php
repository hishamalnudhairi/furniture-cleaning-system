@extends('layouts.app')

@section('title', __('Order details'))

@section('content')
    @php
        $statusMeta = [
            'new' => ['label' => __('New'), 'class' => 'bg-slate-100 text-slate-700', 'dot' => 'bg-slate-400'],
            'cleaning' => ['label' => __('Cleaning'), 'class' => 'bg-sky-100 text-sky-800', 'dot' => 'bg-sky-500'],
            'ready' => ['label' => __('Ready for delivery'), 'class' => 'bg-emerald-100 text-emerald-800', 'dot' => 'bg-emerald-500'],
            'delivered' => ['label' => __('Delivered'), 'class' => 'bg-brand-100 text-brand-800', 'dot' => 'bg-brand-500'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-rose-100 text-rose-700', 'dot' => 'bg-rose-500'],
        ];
        $payMeta = [
            'unpaid' => ['label' => __('Unpaid'), 'class' => 'bg-rose-100 text-rose-700', 'dot' => 'bg-rose-500'],
            'partial' => ['label' => __('Partial'), 'class' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-500'],
            'paid' => ['label' => __('Paid'), 'class' => 'bg-emerald-100 text-emerald-800', 'dot' => 'bg-emerald-500'],
        ];
        $methods = ['cash' => __('Cash'), 'card' => __('Card'), 'transfer' => __('Transfer'), 'other' => __('Other service')];
        $sr = $order->serviceRequest;
        $mapUrl = $order->location_url ?: (($order->latitude && $order->longitude)
            ? 'https://www.google.com/maps?q='.$order->latitude.','.$order->longitude : null);
        // نسبة السداد لشريط التقدّم المالي
        $payPct = (float) $order->total > 0
            ? min(100, round(((float) $order->paid_amount / (float) $order->total) * 100))
            : 0;
    @endphp

    {{-- شريط علوي: رجوع + الحالتان --}}
    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back to list') }}</a>
        <div class="flex flex-wrap justify-end gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold {{ $statusMeta[$order->status]['class'] ?? 'bg-slate-100' }}">
                <span class="h-2 w-2 rounded-full {{ $statusMeta[$order->status]['dot'] ?? 'bg-slate-400' }}"></span>{{ $statusMeta[$order->status]['label'] ?? $order->status }}
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold {{ $payMeta[$order->payment_status]['class'] ?? 'bg-slate-100' }}">
                <span class="h-2 w-2 rounded-full {{ $payMeta[$order->payment_status]['dot'] ?? 'bg-slate-400' }}"></span>{{ $payMeta[$order->payment_status]['label'] ?? $order->payment_status }}
            </span>
        </div>
    </div>

    <h1 class="mb-4 text-2xl font-bold text-brand-700 tracking-wide">{{ $order->order_number }}</h1>

    @include('partials.flash')

    {{-- تحذير المتبقي --}}
    @if ((float) $order->due_amount > 0)
        <div class="mb-4 flex items-start gap-3 rounded-xl bg-amber-50 p-4 text-sm font-semibold text-amber-900 ring-1 ring-amber-200">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-white">!</span>
            <p class="pt-0.5">{{ __('There is a remaining balance on this customer.') }} — {{ __('Remaining') }}: <span class="tabular-nums">{{ number_format((float) $order->due_amount, 2) }}</span></p>
        </div>
    @endif

    {{-- ===== 1) بيانات العميل ===== --}}
    <section class="mb-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-base">👤</span>{{ __('Customer') }}
        </h2>
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-400">{{ __('Full name') }}</dt><dd class="font-medium text-slate-800">{{ $order->customer?->name }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Phone number') }}</dt><dd class="font-medium text-slate-800" dir="ltr">{{ $order->customer?->phone }}</dd></div>
            @if ($sr)
                <div><dt class="text-slate-400">{{ __('Wilaya') }}</dt><dd class="text-slate-800">{{ $sr->wilaya ?: '—' }}</dd></div>
                <div><dt class="text-slate-400">{{ __('Area / Village') }}</dt><dd class="text-slate-800">{{ $sr->area ?: '—' }}</dd></div>
            @endif
            <div class="sm:col-span-2"><dt class="text-slate-400">{{ __('Detailed address') }}</dt><dd class="text-slate-800">{{ $sr->address ?? $order->customer?->address ?: '—' }}</dd></div>
        </dl>
        @if ($mapUrl)
            <a href="{{ $mapUrl }}" target="_blank" rel="noopener"
               class="mt-4 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">📍 {{ __('Open in Google Maps') }}</a>
        @endif
    </section>

    {{-- ===== 2) الخدمة (البنود) ===== --}}
    <section class="mb-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex items-center justify-between gap-2">
            <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-base">🧺</span>{{ __('Requested services') }}
            </h2>
            <a href="{{ route('admin.orders.edit', $order) }}" class="text-sm font-medium text-brand-700 hover:underline">✎ {{ __('Edit order') }}</a>
        </div>
        <div class="space-y-2 text-sm">
            @foreach ($order->items as $item)
                <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-3">
                    <div class="min-w-0">
                        <p class="font-medium text-slate-800">{{ $item->description }}</p>
                        <p class="text-xs text-slate-500 tabular-nums">{{ number_format((float) $item->quantity, 2) }} × {{ number_format((float) $item->unit_price, 2) }}</p>
                    </div>
                    <span class="shrink-0 font-semibold text-slate-700 tabular-nums">{{ number_format((float) $item->line_total, 2) }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== 3) الدفع (الملخص المالي + المدفوعات + تسجيل دفعة) ===== --}}
    <section class="mb-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-base">💰</span>{{ __('Payment') }}
        </h2>

        {{-- الملخص المالي --}}
        <div class="rounded-xl bg-slate-50 p-4">
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span class="tabular-nums">{{ number_format((float) $order->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Discount') }}</span><span class="tabular-nums">{{ number_format((float) $order->discount, 2) }}</span></div>
                <div class="flex justify-between border-t border-slate-200 pt-1.5 text-base"><span class="font-semibold">{{ __('Total') }}</span><span class="font-bold text-brand-700 tabular-nums">{{ number_format((float) $order->total, 2) }}</span></div>
            </div>

            {{-- شريط تقدّم السداد --}}
            <div class="mt-3">
                <div class="h-2 overflow-hidden rounded-full bg-slate-200">
                    <div class="h-full rounded-full {{ (float) $order->due_amount > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ $payPct }}%"></div>
                </div>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-slate-500">{{ __('Paid') }}: <span class="font-medium text-slate-700 tabular-nums">{{ number_format((float) $order->paid_amount, 2) }}</span></span>
                    <span class="font-bold tabular-nums {{ (float) $order->due_amount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ __('Remaining') }}: {{ number_format((float) $order->due_amount, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- المدفوعات السابقة --}}
        <div class="mt-4">
            <h3 class="mb-2 text-sm font-semibold text-slate-600">{{ __('Previous payments') }}</h3>
            @forelse ($order->payments as $payment)
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0">
                    <div>
                        <span class="font-semibold text-slate-800 tabular-nums">{{ number_format((float) $payment->amount, 2) }}</span>
                        <span class="text-slate-400">· {{ $methods[$payment->method] ?? $payment->method }}</span>
                    </div>
                    <span class="text-xs text-slate-400">{{ optional($payment->paid_at)->format('Y-m-d H:i') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">{{ __('No payments yet.') }}</p>
            @endforelse
        </div>

        {{-- تسجيل دفعة (تختفي للطلب الملغي أو المدفوع بالكامل) --}}
        @if (!$order->isCancelled() && (float) $order->due_amount > 0)
            <div class="mt-4 border-t border-slate-100 pt-4">
                <h3 class="mb-3 text-sm font-semibold text-slate-600">{{ __('Record a payment') }}</h3>
                <form method="POST" action="{{ route('admin.orders.payments.store', $order) }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Amount') }}</label>
                        <input name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" required class="field">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Payment method') }}</label>
                        <select name="payment_method" class="field">
                            @foreach (['cash' => __('Cash'), 'card' => __('Card'), 'transfer' => __('Transfer')] as $val => $label)
                                <option value="{{ $val }}" @selected(old('payment_method') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Paid at') }}</label>
                        <input name="paid_at" type="date" value="{{ old('paid_at') }}" class="field">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                        <input name="notes" type="text" value="{{ old('notes') }}" class="field">
                    </div>
                    <div class="sm:col-span-2">
                        <button class="btn btn-primary w-full">{{ __('Save payment') }}</button>
                    </div>
                </form>
                @error('amount')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        @endif

        {{-- الفاتورة والطباعة --}}
        <div class="mt-4 border-t border-slate-100 pt-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-600">{{ __('Invoice & printing') }}</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" rel="noopener"
                   class="btn btn-dark">🧾 {{ __('Print A4 invoice') }}</a>
                <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank" rel="noopener"
                   class="btn btn-soft">🧾 {{ __('Print thermal receipt') }}</a>
            </div>
        </div>
    </section>

    {{-- ===== 4) الحالة (تغيير الحالة) ===== --}}
    @if (!$order->isCancelled())
        <section class="mb-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-base">🔄</span>{{ __('Change status') }}
            </h2>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @php
                    $actions = [
                        'cleaning' => ['label' => __('Start cleaning'), 'class' => 'bg-sky-600 hover:bg-sky-700'],
                        'ready' => ['label' => __('Mark ready'), 'class' => 'bg-emerald-600 hover:bg-emerald-700'],
                        'delivered' => ['label' => __('Mark delivered'), 'class' => 'bg-brand-600 hover:bg-brand-700'],
                    ];
                @endphp
                @foreach ($actions as $value => $meta)
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}"
                          @if ($value === 'delivered' && (float) $order->due_amount > 0)
                              onsubmit="return confirm('{{ __('There is a remaining balance on this customer.') }} {{ __('Continue?') }}')"
                          @endif>
                        @csrf
                        <input type="hidden" name="status" value="{{ $value }}">
                        <button class="w-full rounded-lg px-3 py-2.5 text-sm font-semibold text-white {{ $meta['class'] }} {{ $order->status === $value ? 'opacity-50' : '' }}">{{ $meta['label'] }}</button>
                    </form>
                @endforeach

                <form method="POST" action="{{ route('admin.orders.status', $order) }}"
                      onsubmit="return confirm('{{ __('Are you sure you want to cancel this order?') }}')">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button class="w-full rounded-lg bg-rose-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">{{ __('Cancel order') }}</button>
                </form>
            </div>
        </section>
    @else
        <div class="mb-4 flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm font-medium text-rose-700 ring-1 ring-rose-200">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white">✕</span>
            <p class="pt-0.5">{{ __('This order is cancelled. No further changes are allowed.') }}</p>
        </div>
    @endif

    {{-- ===== التوصيل والاستلام ===== --}}
    <section class="mb-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-base">🚚</span>{{ __('Delivery & pickup') }}
        </h2>

        @php
            $taskStatus = [
                'pending' => ['label' => __('Pending'), 'class' => 'bg-amber-100 text-amber-800'],
                'completed' => ['label' => __('Completed'), 'class' => 'bg-emerald-100 text-emerald-800'],
                'failed' => ['label' => __('Failed'), 'class' => 'bg-rose-100 text-rose-700'],
                'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-slate-200 text-slate-600'],
            ];
            $taskTypes = ['pickup' => __('Pickup'), 'delivery' => __('Delivery'), 'pickup_and_delivery' => __('Pickup & delivery')];
        @endphp

        {{-- المهام الحالية --}}
        @if ($order->deliveryTasks->isNotEmpty())
            <div class="mb-4 space-y-2">
                @foreach ($order->deliveryTasks as $task)
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-3 text-sm">
                        <div>
                            <span class="font-medium text-slate-800">{{ $taskTypes[$task->type] ?? $task->type }}</span>
                            <span class="text-slate-400">· {{ $task->driver?->name ?? '—' }}</span>
                            <p class="text-xs text-slate-500 tabular-nums">{{ __('Driver due') }}: {{ number_format((float) $task->driver_fee, 2) }} · {{ __('Customer fee') }}: {{ number_format((float) $task->customer_fee, 2) }}</p>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $taskStatus[$task->status]['class'] ?? 'bg-slate-100' }}">{{ $taskStatus[$task->status]['label'] ?? $task->status }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- إنشاء مهمة جديدة --}}
        @if (!$order->isCancelled())
            @if ($activeDrivers->isEmpty())
                <p class="text-sm text-slate-400">{{ __('No active drivers. Add a driver first.') }}</p>
            @else
                <form method="POST" action="{{ route('admin.orders.delivery-tasks.store', $order) }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Task type') }}</label>
                        <select name="type" class="field">
                            <option value="pickup">{{ __('Pickup') }}</option>
                            <option value="delivery" selected>{{ __('Delivery') }}</option>
                            <option value="pickup_and_delivery">{{ __('Pickup & delivery') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Driver') }}</label>
                        <select name="driver_id" class="field">
                            @foreach ($activeDrivers as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Customer fee') }}</label>
                        <input name="customer_fee" type="number" min="0" step="0.01" value="0" class="field">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Driver due') }}</label>
                        <input name="driver_fee" type="number" min="0" step="0.01" placeholder="{{ __('Default delivery fee') }}" class="field">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Task date') }}</label>
                        <input name="scheduled_at" type="date" class="field">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                        <input name="notes" type="text" class="field">
                    </div>
                    <div class="sm:col-span-2">
                        <button class="btn btn-primary w-full">{{ __('Create delivery task') }}</button>
                    </div>
                </form>
            @endif
        @endif
    </section>

    {{-- ملاحظات + معلومات --}}
    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-base">📝</span>{{ __('Order info') }}
        </h2>
        @if ($order->notes)
            <p class="mb-2 text-sm text-slate-700">{{ __('Order notes') }}: {{ $order->notes }}</p>
        @endif
        <p class="text-xs text-slate-400">{{ __('Created at') }}: {{ $order->created_at->format('Y-m-d H:i') }}</p>
        @if ($sr)
            <p class="mt-1 text-xs text-slate-400">
                {{ __('Source request') }}:
                <a href="{{ route('admin.service-requests.show', $sr) }}" class="text-brand-600 hover:underline">{{ $sr->request_number }}</a>
            </p>
        @endif
    </section>
@endsection
