@extends('layouts.admin')

@section('title', 'Forbidden')

@section('content')
    <div class="rounded-lg bg-white p-10 text-center shadow">
        <p class="text-sm font-semibold uppercase tracking-wider text-red-600">403</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">Forbidden</h1>
        <p class="mt-2 text-sm text-slate-600">
            {{ isset($exception) && $exception->getMessage() ? $exception->getMessage() : 'You do not have permission to access this page.' }}
        </p>
        <a href="{{ route('web-admin.dashboard') }}" class="mt-6 inline-block rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            Back to dashboard
        </a>
    </div>
@endsection
