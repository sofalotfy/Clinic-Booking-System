@extends('layouts.admin')

@section('title', 'Edit Cost Category')

@section('content')
    <h1 class="mb-8 text-2xl font-semibold text-slate-900">Edit Cost Category</h1>

    <form method="POST" action="{{ route('web-admin.cost-categories.update', $category['id']) }}">
        @csrf
        @method('PUT')
        @include('admin.cost-categories.partials.form', ['category' => $category])
    </form>
@endsection
