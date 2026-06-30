@extends('layouts.app')

@section('title', __('Edit driver'))

@section('content')
    <a href="{{ route('admin.drivers.show', $driver) }}" class="text-sm text-slate-500 hover:text-slate-700">← {{ __('Back') }}</a>
    <h1 class="mb-4 mt-2 text-2xl font-bold text-slate-900">{{ __('Edit driver') }} — {{ $driver->name }}</h1>

    <form method="POST" action="{{ route('admin.drivers.update', $driver) }}">
        @csrf
        @method('PUT')
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            @include('admin.drivers._form')
        </section>
        <button type="submit" class="mt-5 w-full rounded-xl bg-teal-600 px-4 py-3 text-base font-semibold text-white hover:bg-teal-700">{{ __('Save changes') }}</button>
    </form>
@endsection
