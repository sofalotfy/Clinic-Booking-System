@extends('layouts.admin')

@section('title', 'Add Lab')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Add Lab</h1>

    <form method="POST" action="{{ route('web-admin.labs.store') }}">
        @csrf
        @include('admin.labs.partials.form')
    </form>
@endsection
