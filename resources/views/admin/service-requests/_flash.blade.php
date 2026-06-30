@if (session('success'))
    <div class="mb-4 rounded-lg bg-teal-50 p-4 text-sm text-teal-800 ring-1 ring-teal-200">
        <p class="font-medium">{{ session('success') }}</p>
        @if (session('order_number'))
            <p class="mt-1">
                {{ __('Official order number') }}:
                <span class="font-bold tracking-wider">{{ session('order_number') }}</span>
            </p>
        @endif
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">
        {{ session('error') }}
    </div>
@endif
