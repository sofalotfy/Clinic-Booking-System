@extends('layouts.admin')

@section('title', 'Add Scan')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Add Scan</h1>

    <form method="POST" action="{{ route('web-admin.scans.store') }}">
        @csrf
        @include('admin.scans.partials.form')
    </form>
@endsection
