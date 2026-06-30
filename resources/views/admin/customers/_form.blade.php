@php
    $customer = $customer ?? null;
    $type = old('customer_type', $customer->customer_type ?? '');
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
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Full name') }}</label>
        <input name="name" value="{{ old('name', $customer->name ?? '') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Phone number') }}</label>
        <input name="phone" value="{{ old('phone', $customer->phone ?? '') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Customer type') }}</label>
        <select name="customer_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">{{ __('— Optional —') }}</option>
            @foreach (['individual' => __('Individual'), 'company' => __('Company'), 'mosque' => __('Mosque'), 'organization' => __('Organization')] as $val => $label)
                <option value="{{ $val }}" @selected($type === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Wilaya') }}</label>
        <input name="wilaya" value="{{ old('wilaya', $customer->wilaya ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Area / Village') }}</label>
        <input name="area" value="{{ old('area', $customer->area ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Detailed address') }}</label>
        <input name="address" value="{{ old('address', $customer->address ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Latitude') }}</label>
        <input name="latitude" value="{{ old('latitude', $customer->latitude ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Longitude') }}</label>
        <input name="longitude" value="{{ old('longitude', $customer->longitude ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Or paste a Google Maps link') }}</label>
        <input name="location_url" value="{{ old('location_url', $customer->location_url ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
        <textarea name="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('notes', $customer->notes ?? '') }}</textarea>
    </div>
</div>
