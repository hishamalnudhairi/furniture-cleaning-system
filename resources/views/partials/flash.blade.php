{{-- رسائل النظام: نجاح / تحذير / خطأ — بتصميم موحّد وواضح ومناسب للـ RTL --}}
@php
    $flashTypes = [
        'success' => [
            'icon'  => '✓',
            'wrap'  => 'bg-emerald-50 ring-emerald-200 text-emerald-800',
            'badge' => 'bg-emerald-600 text-white',
        ],
        'warning' => [
            'icon'  => '!',
            'wrap'  => 'bg-amber-50 ring-amber-200 text-amber-900',
            'badge' => 'bg-amber-500 text-white',
        ],
        'error' => [
            'icon'  => '✕',
            'wrap'  => 'bg-rose-50 ring-rose-200 text-rose-800',
            'badge' => 'bg-rose-600 text-white',
        ],
    ];
@endphp

@foreach (['success', 'warning', 'error'] as $type)
    @if (session($type))
        @php $f = $flashTypes[$type]; @endphp
        <div role="alert"
             class="mb-4 flex items-start gap-3 rounded-xl p-4 text-sm font-medium shadow-sm ring-1 {{ $f['wrap'] }}">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $f['badge'] }}">{{ $f['icon'] }}</span>
            <p class="pt-0.5 leading-relaxed">{{ session($type) }}</p>
        </div>
    @endif
@endforeach
