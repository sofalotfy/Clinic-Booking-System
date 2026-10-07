@extends('layouts.admin')

@section('title', 'Add Doctor')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Add Doctor</h1>

    <form method="POST" action="{{ route('web-admin.doctors.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.doctors.partials.form')
    </form>
@endsection