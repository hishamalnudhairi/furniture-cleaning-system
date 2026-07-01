@extends('layouts.app')

@section('title', __('Add item'))

@section('content')
    <a href="{{ route('admin.inventory.index') }}" class="text-sm text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back') }}</a>
    <h1 class="mb-4 mt-2 text-2xl font-bold text-slate-900">{{ __('Add item') }}</h1>

    <form method="POST" action="{{ route('admin.inventory.store') }}">
        @csrf
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            @include('admin.inventory._form')
        </section>
        <button type="submit" class="mt-5 w-full rounded-xl bg-brand-600 px-4 py-3 text-base font-semibold text-white hover:bg-brand-700">{{ __('Save') }}</button>
    </form>
@endsection
