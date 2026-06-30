@extends('layouts.app')

@section('title', __('Customer Requests'))

@section('content')
    @php
        $statusMeta = [
            'pending_review' => ['label' => __('Pending review'), 'class' => 'bg-amber-100 text-amber-800'],
            'contacted' => ['label' => __('Contacted'), 'class' => 'bg-sky-100 text-sky-800'],
            'confirmed' => ['label' => __('Confirmed'), 'class' => 'bg-teal-100 text-teal-800'],
            'cancelled' => ['label' => __('Cancelled'), 'class' => 'bg-rose-100 text-rose-700'],
        ];
        $tabs = [
            '' => __('All'),
            'pending_review' => __('Pending review'),
            'contacted' => __('Contacted'),
            'confirmed' => __('Confirmed'),
            'cancelled' => __('Cancelled'),
        ];
    @endphp

    <h1 class="mb-4 text-2xl font-bold text-slate-900">{{ __('Customer Requests') }}</h1>

    @include('admin.service-requests._flash')

    {{-- الفلاتر --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($tabs as $value => $label)
            <a href="{{ route('admin.service-requests.index', array_filter(['status' => $value, 'q' => $search])) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ (string) $status === (string) $value ? 'bg-teal-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- البحث --}}
    <form method="GET" action="{{ route('admin.service-requests.index') }}" class="mb-5 flex gap-2">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <input name="q" type="text" value="{{ $search }}"
               placeholder="{{ __('Search by name, phone, or request no.') }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">{{ __('Search') }}</button>
    </form>

    {{-- القائمة --}}
    @forelse ($requests as $req)
        <div class="mb-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-teal-700">{{ $req->request_number }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusMeta[$req->status]['class'] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $statusMeta[$req->status]['label'] ?? $req->status }}
                        </span>
                    </div>
                    <p class="mt-1 font-medium text-slate-800">{{ $req->customer_name }}</p>
                    <p class="text-sm text-slate-500">{{ $req->customer_phone }}</p>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ $req->wilaya }}@if ($req->area) — {{ $req->area }}@endif
                    </p>
                    @if ($summary = $req->servicesSummary())
                        <p class="mt-1 text-xs text-slate-500">🧺 {{ \Illuminate\Support\Str::limit($summary, 60) }}</p>
                    @endif
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                        @if ($req->preferred_date)
                            <span>📅 {{ $req->preferred_date->format('Y-m-d') }}</span>
                        @endif
                        <span>🕒 {{ $req->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
                <a href="{{ route('admin.service-requests.show', $req) }}"
                   class="shrink-0 rounded-lg bg-teal-600 px-3 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    {{ __('Details') }}
                </a>
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white p-8 text-center text-slate-500 ring-1 ring-slate-200">
            {{ __('No requests found.') }}
        </div>
    @endforelse

    <div class="mt-4">
        {{ $requests->links() }}
    </div>
@endsection
