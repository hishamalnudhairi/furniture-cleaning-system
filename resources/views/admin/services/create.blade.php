@extends('layouts.app')

@section('title', __('Add service'))

@section('content')
    <a href="{{ route('admin.services.index') }}" class="text-sm text-slate-500 hover:text-slate-700"><span class="ib-flip">←</span> {{ __('Back') }}</a>
    <h1 class="mb-4 mt-2 text-2xl font-bold text-slate-900">{{ __('Add service') }}</h1>

    <form method="POST" action="{{ route('admin.services.store') }}">
        @csrf
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            @include('admin.services._form')
        </section>
        <button type="submit" class="mt-5 btn btn-primary w-full text-base">{{ __('Save') }}</button>
    </form>
@endsection
