@extends('layouts.admin')

@section('title', 'Roles')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Roles</h1>
        @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::CREATE_ROLE))
            <a href="{{ route('web-admin.roles.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Add Role
            </a>
        @endif
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Users</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($roles as $role)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $role['name'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $role['users_count'] }}</td>
                        <td class="px-6 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_SINGLE_ROLE))
                                    <a href="{{ route('web-admin.roles.show', $role['id']) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                @endif

                                @unless ($role['protected'])
                                    @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_ROLE))
                                        <a href="{{ route('web-admin.roles.edit', $role['id']) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                    @endif

                                    @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::DELETE_ROLE))
                                        <form
                                            method="POST"
                                            action="{{ route('web-admin.roles.destroy', $role['id']) }}"
                                            onsubmit="return confirm('Delete this role?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    @endif
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-slate-500">No roles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($roles->hasPages())
        <div class="mt-6">
            {{ $roles->links() }}
        </div>
    @endif
@endsection
