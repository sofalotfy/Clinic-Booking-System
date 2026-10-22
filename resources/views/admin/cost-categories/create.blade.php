@extends('layouts.admin')

@section('title', 'Add Cost Category')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Add Cost Category</h1>

    <form method="POST" action="{{ route('web-admin.cost-categories.store') }}">
        @csrf
        @include('admin.cost-categories.partials.form')
    </form>
@endsection
