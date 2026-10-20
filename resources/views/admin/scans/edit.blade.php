@extends('layouts.admin')

@section('title', 'Edit Scan')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Edit Scan</h1>

    <form method="POST" action="{{ route('web-admin.scans.update', $scan['id']) }}">
        @csrf
        @method('PUT')
        @include('admin.scans.partials.form', ['scan' => $scan])
    </form>
@endsection
