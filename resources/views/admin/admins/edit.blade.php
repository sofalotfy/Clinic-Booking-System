@extends('layouts.admin')

@section('title', 'Edit Admin')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Edit Admin</h1>

    <form method="POST" action="{{ route('web-admin.admins.update', $admin['id']) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.admins.partials.form', ['admin' => $admin])
    </form>
@endsection
