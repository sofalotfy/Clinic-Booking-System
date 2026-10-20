@extends('layouts.admin')

@section('title', 'Labs')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Labs</h1>
        @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::CREATE_LAB))
            <a href="{{ route('web-admin.labs.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Add Lab
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('web-admin.labs.index') }}" class="mb-6 flex gap-3">
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search by name, Arabic name or code"
            class="w-full max-w-md rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
        >
        <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Search</button>
    </form>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Arabic Name</th>
                    <th class="px-6 py-3">Code</th>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Sample</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($labs as $lab)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $lab['name'] }}</td>
                        <td class="px-6 py-3 text-slate-600" dir="rtl">{{ $lab['name_ar'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $lab['code'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $lab['category'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $lab['sample_type'] ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @if ($lab['is_active'])
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_SINGLE_LAB))
                                    <a href="{{ route('web-admin.labs.show', $lab['id']) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                @endif

                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_LAB))
                                    <a href="{{ route('web-admin.labs.edit', $lab['id']) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                @endif

                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::DELETE_LAB))
                                    <form
                                        method="POST"
                                        action="{{ route('web-admin.labs.destroy', $lab['id']) }}"
                                        onsubmit="return confirm('Delete this lab? Prescriptions that already use it are kept.');"
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
                        <td colspan="7" class="px-6 py-10 text-center text-slate-500">No labs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($labs->hasPages())
        <div class="mt-6">
            {{ $labs->links() }}
        </div>
    @endif
@endsection
