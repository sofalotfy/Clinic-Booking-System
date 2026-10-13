@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Edit Role</h1>

    <form method="POST" action="{{ route('web-admin.roles.update', $role['id']) }}">
        @csrf
        @method('PUT')
        @include('admin.roles.partials.form', ['role' => $role])
    </form>
@endsection
