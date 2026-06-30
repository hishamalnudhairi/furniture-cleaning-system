@extends('layouts.app')

@section('title', __('Edit order'))

@section('content')
    <a href="{{ route('admin.orders.show', $order) }}" class="text-sm text-slate-500 hover:text-slate-700">← {{ __('Back') }}</a>
    <h1 class="mb-1 mt-2 text-2xl font-bold text-slate-900">{{ __('Edit order') }} — {{ $order->order_number }}</h1>
    <p class="mb-4 text-sm text-slate-500">{{ __('You can edit the discount and the order notes only.') }}</p>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4 space-y-1 rounded-lg bg-slate-50 p-4 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span id="o-subtotal" data-value="{{ (float) $order->subtotal }}">{{ number_format((float) $order->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-base"><span class="font-semibold">{{ __('Total') }}</span><span id="o-total" class="font-bold text-teal-700">{{ number_format((float) $order->total, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Paid') }}</span><span data-value="{{ (float) $order->paid_amount }}" id="o-paid">{{ number_format((float) $order->paid_amount, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Remaining') }}</span><span id="o-remaining" class="font-bold text-rose-600">{{ number_format((float) $order->due_amount, 2) }}</span></div>
            </div>

            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Discount') }}</label>
                <input id="discount" name="discount" type="number" min="0" step="0.01" value="{{ old('discount', (float) $order->discount) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Order notes') }}</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('notes', $order->notes) }}</textarea>
            </div>
        </section>

        <button type="submit" class="w-full rounded-xl bg-teal-600 px-4 py-3 text-base font-semibold text-white hover:bg-teal-700">{{ __('Save changes') }}</button>
    </form>

    <script>
        (function () {
            var subtotal = parseFloat(document.getElementById('o-subtotal').dataset.value) || 0;
            var paid = parseFloat(document.getElementById('o-paid').dataset.value) || 0;
            var discountEl = document.getElementById('discount');

            function recalc() {
                var d = parseFloat(discountEl.value) || 0;
                if (d > subtotal) d = subtotal;
                var total = subtotal - d;
                var remaining = total - paid;
                document.getElementById('o-total').textContent = total.toFixed(2);
                document.getElementById('o-remaining').textContent = remaining.toFixed(2);
            }
            discountEl.addEventListener('input', recalc);
            recalc();
        })();
    </script>
@endsection
