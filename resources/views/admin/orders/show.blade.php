@extends('layouts.app')

@section('title', __('Order details'))

@section('content')
    @php
        $statusMeta = [
            'new' => ['label' => __('New'), 'class' => 'bg-slate-100 text-slate-700'],
            'cleaning' => ['label' => __('Cleaning'), 'class' => 'bg-sky-100 text-sky-800'],
            'ready' => ['label' => __('Ready for delivery'), 'class' => 'bg-emerald-100 text-emerald-800'],
            'delivered' => ['label' => __('Delivered'), 'class' => 'bg-teal-100 text-teal-800'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-rose-100 text-rose-700'],
        ];
        $payMeta = [
            'unpaid' => ['label' => __('Unpaid'), 'class' => 'bg-rose-100 text-rose-700'],
            'partial' => ['label' => __('Partial'), 'class' => 'bg-amber-100 text-amber-800'],
            'paid' => ['label' => __('Paid'), 'class' => 'bg-emerald-100 text-emerald-800'],
        ];
        $methods = ['cash' => __('Cash'), 'card' => __('Card'), 'transfer' => __('Transfer'), 'other' => __('Other service')];
        $sr = $order->serviceRequest;
        $mapUrl = $order->location_url ?: (($order->latitude && $order->longitude)
            ? 'https://www.google.com/maps?q='.$order->latitude.','.$order->longitude : null);
    @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← {{ __('Back to list') }}</a>
        <div class="flex gap-2">
            <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusMeta[$order->status]['class'] ?? 'bg-slate-100' }}">{{ $statusMeta[$order->status]['label'] ?? $order->status }}</span>
            <span class="rounded-full px-3 py-1 text-sm font-medium {{ $payMeta[$order->payment_status]['class'] ?? 'bg-slate-100' }}">{{ $payMeta[$order->payment_status]['label'] ?? $order->payment_status }}</span>
        </div>
    </div>

    <h1 class="mb-4 text-2xl font-bold text-teal-700">{{ $order->order_number }}</h1>

    @include('partials.flash')

    {{-- تحذير المتبقي --}}
    @if ((float) $order->due_amount > 0)
        <div class="mb-4 rounded-lg bg-amber-50 p-4 text-sm font-semibold text-amber-800 ring-1 ring-amber-200">
            ⚠️ {{ __('There is a remaining balance on this customer.') }} — {{ __('Remaining') }}: {{ number_format((float) $order->due_amount, 2) }}
        </div>
    @endif

    {{-- العميل --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Customer') }}</h2>
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-400">{{ __('Full name') }}</dt><dd class="font-medium text-slate-800">{{ $order->customer?->name }}</dd></div>
            <div><dt class="text-slate-400">{{ __('Phone number') }}</dt><dd class="font-medium text-slate-800">{{ $order->customer?->phone }}</dd></div>
            @if ($sr)
                <div><dt class="text-slate-400">{{ __('Wilaya') }}</dt><dd class="text-slate-800">{{ $sr->wilaya ?: '—' }}</dd></div>
                <div><dt class="text-slate-400">{{ __('Area / Village') }}</dt><dd class="text-slate-800">{{ $sr->area ?: '—' }}</dd></div>
            @endif
            <div class="sm:col-span-2"><dt class="text-slate-400">{{ __('Detailed address') }}</dt><dd class="text-slate-800">{{ $sr->address ?? $order->customer?->address ?: '—' }}</dd></div>
        </dl>
        @if ($mapUrl)
            <a href="{{ $mapUrl }}" target="_blank" rel="noopener"
               class="mt-3 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">📍 {{ __('Open in Google Maps') }}</a>
        @endif
    </section>

    {{-- البنود --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Requested services') }}</h2>
        <div class="space-y-2 text-sm">
            @foreach ($order->items as $item)
                <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-3">
                    <div class="min-w-0">
                        <p class="font-medium text-slate-800">{{ $item->description }}</p>
                        <p class="text-xs text-slate-500">{{ number_format((float) $item->quantity, 2) }} × {{ number_format((float) $item->unit_price, 2) }}</p>
                    </div>
                    <span class="shrink-0 font-semibold text-slate-700">{{ number_format((float) $item->line_total, 2) }}</span>
                </div>
            @endforeach
        </div>

        {{-- الملخص المالي --}}
        <div class="mt-4 space-y-1 border-t border-slate-200 pt-3 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span>{{ number_format((float) $order->subtotal, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Discount') }}</span><span>{{ number_format((float) $order->discount, 2) }}</span></div>
            <div class="flex justify-between text-base"><span class="font-semibold">{{ __('Total') }}</span><span class="font-bold text-teal-700">{{ number_format((float) $order->total, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Paid') }}</span><span>{{ number_format((float) $order->paid_amount, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Remaining') }}</span><span class="font-bold {{ (float) $order->due_amount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format((float) $order->due_amount, 2) }}</span></div>
        </div>

        <a href="{{ route('admin.orders.edit', $order) }}" class="mt-3 inline-block text-sm font-medium text-teal-700 hover:underline">✎ {{ __('Edit order') }}</a>
    </section>

    {{-- الفاتورة والطباعة --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Invoice & printing') }}</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" rel="noopener"
               class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">🧾 {{ __('Print A4 invoice') }}</a>
            <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank" rel="noopener"
               class="rounded-lg bg-slate-600 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">🧾 {{ __('Print thermal receipt') }}</a>
        </div>
    </section>

    {{-- المدفوعات السابقة --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Previous payments') }}</h2>
        @forelse ($order->payments as $payment)
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0">
                <div>
                    <span class="font-semibold text-slate-800">{{ number_format((float) $payment->amount, 2) }}</span>
                    <span class="text-slate-400">· {{ $methods[$payment->method] ?? $payment->method }}</span>
                </div>
                <span class="text-xs text-slate-400">{{ optional($payment->paid_at)->format('Y-m-d H:i') }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">{{ __('No payments yet.') }}</p>
        @endforelse
    </section>

    {{-- تسجيل دفعة + الإجراءات (تختفي للطلب الملغي) --}}
    @if (!$order->isCancelled())
        @if ((float) $order->due_amount > 0)
            <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Record a payment') }}</h2>
                <form method="POST" action="{{ route('admin.orders.payments.store', $order) }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Amount') }}</label>
                        <input name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Payment method') }}</label>
                        <select name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (['cash' => __('Cash'), 'card' => __('Card'), 'transfer' => __('Transfer')] as $val => $label)
                                <option value="{{ $val }}" @selected(old('payment_method') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Paid at') }}</label>
                        <input name="paid_at" type="date" value="{{ old('paid_at') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                        <input name="notes" type="text" value="{{ old('notes') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <button class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">{{ __('Save payment') }}</button>
                    </div>
                </form>
                @error('amount')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </section>
        @endif

        <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Change status') }}</h2>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @php
                    $actions = [
                        'cleaning' => ['label' => __('Start cleaning'), 'class' => 'bg-sky-600 hover:bg-sky-700'],
                        'ready' => ['label' => __('Mark ready'), 'class' => 'bg-emerald-600 hover:bg-emerald-700'],
                        'delivered' => ['label' => __('Mark delivered'), 'class' => 'bg-teal-600 hover:bg-teal-700'],
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
        <div class="mb-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">{{ __('This order is cancelled. No further changes are allowed.') }}</div>
    @endif

    {{-- التوصيل والاستلام --}}
    <section class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Delivery & pickup') }}</h2>

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
                            <p class="text-xs text-slate-500">{{ __('Driver due') }}: {{ number_format((float) $task->driver_fee, 2) }} · {{ __('Customer fee') }}: {{ number_format((float) $task->customer_fee, 2) }}</p>
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
                        <select name="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="pickup">{{ __('Pickup') }}</option>
                            <option value="delivery" selected>{{ __('Delivery') }}</option>
                            <option value="pickup_and_delivery">{{ __('Pickup & delivery') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Driver') }}</label>
                        <select name="driver_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach ($activeDrivers as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Customer fee') }}</label>
                        <input name="customer_fee" type="number" min="0" step="0.01" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Driver due') }}</label>
                        <input name="driver_fee" type="number" min="0" step="0.01" placeholder="{{ __('Default delivery fee') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Task date') }}</label>
                        <input name="scheduled_at" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                        <input name="notes" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <button class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">{{ __('Create delivery task') }}</button>
                    </div>
                </form>
            @endif
        @endif
    </section>

    {{-- ملاحظات + معلومات --}}
    <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-bold text-slate-900">{{ __('Order info') }}</h2>
        @if ($order->notes)
            <p class="mb-2 text-sm text-slate-700">{{ __('Order notes') }}: {{ $order->notes }}</p>
        @endif
        <p class="text-xs text-slate-400">{{ __('Created at') }}: {{ $order->created_at->format('Y-m-d H:i') }}</p>
        @if ($sr)
            <p class="mt-1 text-xs text-slate-400">
                {{ __('Source request') }}:
                <a href="{{ route('admin.service-requests.show', $sr) }}" class="text-teal-600 hover:underline">{{ $sr->request_number }}</a>
            </p>
        @endif
    </section>
@endsection
