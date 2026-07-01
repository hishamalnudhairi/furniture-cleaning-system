@props(['name', 'checked' => false, 'label' => ''])

<label class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2 text-sm">
    <span class="font-medium text-slate-700">{{ $label }}</span>
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))
           class="h-5 w-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
</label>
