@extends('layouts.admin')

@section('title', 'Admin Details')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">{{ $admin['name'] }}</h1>
        <div class="inline-flex items-center gap-3">
            @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_ADMIN))
                <a href="{{ route('web-admin.admins.edit', $admin['id']) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Edit</a>
            @endif
            <a href="{{ $user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_ADMINS) ? route('web-admin.admins.index') : route('web-admin.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back</a>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Account</h2>

        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Name</dt>
                <dd class="font-medium text-slate-900">{{ $admin['name'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Email</dt>
                <dd class="font-medium text-slate-900">{{ $admin['email'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Phone</dt>
                <dd class="font-medium text-slate-900">{{ $admin['phone'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Age</dt>
                <dd class="font-medium text-slate-900">{{ $admin['age'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Gender</dt>
                <dd class="font-medium text-slate-900">{{ $admin['gender'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Area</dt>
                <dd class="font-medium text-slate-900">{{ $admin['area'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Roles</dt>
                <dd class="font-medium text-slate-900">{{ $admin['roles']->isNotEmpty() ? $admin['roles']->implode(', ') : '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Joined</dt>
                <dd class="font-medium text-slate-900">{{ $admin['created_at'] ?? '—' }}</dd>
            </div>
        </dl>
    </div>
@endsection
