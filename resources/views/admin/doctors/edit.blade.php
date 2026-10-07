@extends('layouts.admin')

@section('title', 'Edit Doctor')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Edit Doctor</h1>

    <form method="POST" action="{{ route('web-admin.doctors.update', $doctor['id']) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.doctors.partials.form', ['doctor' => $doctor])
    </form>
@endsection