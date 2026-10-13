@extends('layouts.admin')

@section('title', 'Role Details')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">{{ $role['name'] }}</h1>
        <div class="inline-flex items-center gap-3">
            @unless ($role['protected'])
                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_ROLE))
                    <a href="{{ route('web-admin.roles.edit', $role['id']) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Edit</a>
                @endif
            @endunless
            <a href="{{ $user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_ROLES) ? route('web-admin.roles.index') : route('web-admin.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back</a>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Permissions</h2>

        <div class="flex flex-wrap gap-2">
            @forelse ($role['permissions'] as $permission)
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $permission['name'] }}</span>
            @empty
                <p class="text-sm text-slate-500">No permissions assigned.</p>
            @endforelse
        </div>
    </div>
@endsection
