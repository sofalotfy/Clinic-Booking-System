@extends('layouts.admin')

@section('title', 'Add Medicine')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Add Medicine</h1>

    <form method="POST" action="{{ route('web-admin.medicines.store') }}">
        @csrf
        @include('admin.medicines.partials.form')
    </form>
@endsection
