@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
        <a href="{{ route('web-admin.doctors.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            Add Doctor
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <a href="{{ route('web-admin.doctors.index') }}" class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm font-medium text-slate-500">Doctors</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $doctors }}</p>
        </a>

        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm font-medium text-slate-500">Patients</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $patients }}</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm font-medium text-slate-500">Appointments</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $appointments }}</p>
        </div>
    </div>
@endsection