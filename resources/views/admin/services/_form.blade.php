@php $service = $service ?? null; @endphp

@if ($errors->any())
    <div class="mb-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">
        <ul class="list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Service name (Arabic)') }}</label>
        <input name="name_ar" value="{{ old('name_ar', $service->name_ar ?? '') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Service name (English)') }}</label>
        <input name="name_en" value="{{ old('name_en', $service->name_en ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Unit') }}</label>
        <input name="unit" value="{{ old('unit', $service->unit ?? '') }}" placeholder="{{ __('e.g. piece, m²') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Base price') }}</label>
        <input name="base_price" type="number" step="0.01" min="0" value="{{ old('base_price', $service->default_price ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
        <textarea name="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('notes', $service->notes ?? '') }}</textarea>
    </div>
</div>

<div class="mt-4 space-y-2">
    <x-settings-toggle name="is_price_editable" :checked="$service?->is_price_editable ?? true" :label="__('Price is editable on orders')" />
    <x-settings-toggle name="is_active" :checked="$service?->is_active ?? true" :label="__('Active')" />
</div>
