@php
    $driver = $driver ?? null;
    $settings = $settings ?? null;
    $status = old('status', $driver ? ($driver->is_active ? 'active' : 'inactive') : 'active');
    $paymentType = old('payment_type', $driver->payment_type ?? ($settings->default_driver_payment_type ?? 'per_task'));
    $defaultFee = old('default_delivery_fee', $driver->default_delivery_fee ?? ($settings->default_delivery_fee ?? 0));
@endphp

@if ($errors->any())
    <div class="mb-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">
        <ul class="list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Driver name') }}</label>
        <input name="name" value="{{ old('name', $driver->name ?? '') }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Phone number') }}</label>
        <input name="phone" value="{{ old('phone', $driver->phone ?? '') }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Payment type') }}</label>
        <select name="payment_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="per_task" @selected($paymentType === 'per_task')>{{ __('Per task') }}</option>
            <option value="per_day" @selected($paymentType === 'per_day')>{{ __('Per day') }}</option>
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Default delivery fee') }}</label>
        <input name="default_delivery_fee" type="number" min="0" step="0.01"
               value="{{ $defaultFee }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
            <option value="inactive" @selected($status === 'inactive')>{{ __('Inactive') }}</option>
        </select>
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
        <textarea name="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('notes', $driver->notes ?? '') }}</textarea>
    </div>
</div>
