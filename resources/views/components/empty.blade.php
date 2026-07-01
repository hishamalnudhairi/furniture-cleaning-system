@props(['message' => '', 'icon' => '📭'])

{{-- حالة فارغة موحّدة عبر القوائم --}}
<div class="rounded-2xl bg-white p-10 text-center ring-1 ring-slate-200">
    <p class="text-4xl">{{ $icon }}</p>
    <p class="mt-3 font-medium text-slate-500">{{ $message }}</p>
    @if (! $slot->isEmpty())
        <div class="mt-4 flex justify-center">{{ $slot }}</div>
    @endif
</div>
