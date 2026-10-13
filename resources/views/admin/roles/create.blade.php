@extends('layouts.admin')

@section('title', 'Add Role')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Add Role</h1>

    <form method="POST" action="{{ route('web-admin.roles.store') }}">
        @csrf
        @include('admin.roles.partials.form')
    </form>
@endsection
