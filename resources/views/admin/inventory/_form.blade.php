@php
    $item = $item ?? null;
    $units = ['liter' => __('Liter'), 'bottle' => __('Bottle'), 'piece' => __('Piece'), 'pack' => __('Pack'), 'kg' => __('Kg'), 'other' => __('Other')];
    $status = old('status', $item ? ($item->is_active ? 'active' : 'inactive') : 'active');
    $unit = old('unit', $item->unit ?? 'liter');
@endphp

@include('partials.form-errors')

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Item name') }}</label>
        <input name="name" value="{{ old('name', $item->name ?? '') }}" required
               class="field">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Unit') }}</label>
        <select name="unit" class="field">
            @foreach ($units as $val => $label)
                <option value="{{ $val }}" @selected($unit === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    @if (! $item)
        {{-- الكمية الافتتاحية تُحدَّد عند الإنشاء فقط؛ بعدها تتغير عبر إضافة/صرف --}}
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Opening quantity') }}</label>
            <input name="current_quantity" type="number" min="0" step="0.01" value="{{ old('current_quantity', 0) }}"
                   class="field">
        </div>
    @else
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Current quantity') }}</label>
            <input type="text" value="{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}" readonly
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
            <p class="mt-1 text-xs text-slate-400">{{ __('Change via add / dispense / adjust.') }}</p>
        </div>
    @endif

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Alert quantity') }}</label>
        <input name="alert_quantity" type="number" min="0" step="0.01" value="{{ old('alert_quantity', $item->min_quantity ?? 0) }}"
               class="field">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Purchase price') }}</label>
        <input name="purchase_price" type="number" min="0" step="0.01" value="{{ old('purchase_price', $item->cost_price ?? '') }}"
               class="field">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
        <select name="status" class="field">
            <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
            <option value="inactive" @selected($status === 'inactive')>{{ __('Inactive') }}</option>
        </select>
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
        <textarea name="notes" rows="2" class="field">{{ old('notes', $item->notes ?? '') }}</textarea>
    </div>
</div>
