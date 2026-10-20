@extends('layouts.admin')

@section('title', 'Edit Lab')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Edit Lab</h1>

    <form method="POST" action="{{ route('web-admin.labs.update', $lab['id']) }}">
        @csrf
        @method('PUT')
        @include('admin.labs.partials.form', ['lab' => $lab])
    </form>
@endsection
