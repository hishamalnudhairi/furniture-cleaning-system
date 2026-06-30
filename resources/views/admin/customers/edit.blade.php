@extends('layouts.app')

@section('title', __('Edit customer'))

@section('content')
    <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm text-slate-500 hover:text-slate-700">← {{ __('Back') }}</a>
    <h1 class="mb-4 mt-2 text-2xl font-bold text-slate-900">{{ __('Edit customer') }} — {{ $customer->name }}</h1>

    <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
        @csrf
        @method('PUT')
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            @include('admin.customers._form')
        </section>
        <button type="submit" class="mt-5 w-full rounded-xl bg-teal-600 px-4 py-3 text-base font-semibold text-white hover:bg-teal-700">{{ __('Save changes') }}</button>
    </form>
@endsection
