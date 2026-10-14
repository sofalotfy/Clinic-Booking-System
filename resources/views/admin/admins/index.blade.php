@extends('layouts.admin')

@section('title', 'Admins')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Admins</h1>
        @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::CREATE_ADMIN))
            <a href="{{ route('web-admin.admins.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Add Admin
            </a>
        @endif
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Phone</th>
                    <th class="px-6 py-3">Roles</th>
                    <th class="px-6 py-3">Joined</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($admins as $admin)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $admin['name'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $admin['email'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $admin['phone'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $admin['roles']->isNotEmpty() ? $admin['roles']->implode(', ') : '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $admin['joined'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_SINGLE_ADMIN))
                                    <a href="{{ route('web-admin.admins.show', $admin['id']) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                @endif

                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_ADMIN))
                                    <a href="{{ route('web-admin.admins.edit', $admin['id']) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                @endif

                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::DELETE_ADMIN) && $admin['id'] !== $user->id)
                                    <form
                                        method="POST"
                                        action="{{ route('web-admin.admins.destroy', $admin['id']) }}"
                                        onsubmit="return confirm('Delete this admin?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500">No admins found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($admins->hasPages())
        <div class="mt-6">
            {{ $admins->links() }}
        </div>
    @endif
@endsection
