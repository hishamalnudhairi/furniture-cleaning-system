@if (session('success'))
    <div class="mb-4 rounded-lg bg-brand-50 p-4 text-sm text-brand-800 ring-1 ring-brand-200">
        {{ session('success') }}
    </div>
@endif

@if (session('warning'))
    <div class="mb-4 rounded-lg bg-amber-50 p-4 text-sm font-medium text-amber-800 ring-1 ring-amber-200">
        ⚠️ {{ session('warning') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">
        {{ session('error') }}
    </div>
@endif
