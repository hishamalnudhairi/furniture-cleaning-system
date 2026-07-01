@php $service = $service ?? null; @endphp

@include('partials.form-errors')

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Service name (Arabic)') }}</label>
        <input name="name_ar" value="{{ old('name_ar', $service->name_ar ?? '') }}" required class="field">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Service name (English)') }}</label>
        <input name="name_en" value="{{ old('name_en', $service->name_en ?? '') }}" class="field">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Unit') }}</label>
        <input name="unit" value="{{ old('unit', $service->unit ?? '') }}" placeholder="{{ __('e.g. piece, m²') }}" class="field">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Base price') }}</label>
        <input name="base_price" type="number" step="0.01" min="0" value="{{ old('base_price', $service->default_price ?? '') }}" class="field">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
        <textarea name="notes" rows="2" class="field">{{ old('notes', $service->notes ?? '') }}</textarea>
    </div>
</div>

<div class="mt-4 space-y-2">
    <x-settings-toggle name="is_price_editable" :checked="$service?->is_price_editable ?? true" :label="__('Price is editable on orders')" />
    <x-settings-toggle name="is_active" :checked="$service?->is_active ?? true" :label="__('Active')" />
</div>
