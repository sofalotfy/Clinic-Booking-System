@extends('layouts.admin')

@section('title', 'Edit Medicine')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Edit Medicine</h1>

    <form method="POST" action="{{ route('web-admin.medicines.update', $medicine['id']) }}">
        @csrf
        @method('PUT')
        @include('admin.medicines.partials.form', ['medicine' => $medicine])
    </form>
@endsection
