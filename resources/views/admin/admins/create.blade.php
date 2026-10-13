@extends('layouts.admin')

@section('title', 'Add Admin')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Add Admin</h1>

    <form method="POST" action="{{ route('web-admin.admins.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.admins.partials.form')
    </form>
@endsection
