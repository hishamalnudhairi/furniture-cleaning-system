{{-- ملخّص أخطاء التحقق — بشكل موحّد عبر كل النماذج --}}
@if ($errors->any())
    <div role="alert" class="mb-4 flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-rose-200">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white">✕</span>
        <div>
            <p class="font-semibold">{{ __('Please correct the following:') }}</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif
