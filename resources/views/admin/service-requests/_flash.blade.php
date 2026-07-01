@if (session('success'))
    <div role="alert" class="mb-4 flex items-start gap-3 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm ring-1 ring-emerald-200">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
        <div>
            <p class="font-medium">{{ session('success') }}</p>
            @if (session('order_number'))
                <p class="mt-1">
                    {{ __('Official order number') }}:
                    <span class="font-bold tracking-wider">{{ session('order_number') }}</span>
                </p>
            @endif
        </div>
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-4 flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 shadow-sm ring-1 ring-rose-200">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white">✕</span>
        <p class="pt-0.5">{{ session('error') }}</p>
    </div>
@endif
