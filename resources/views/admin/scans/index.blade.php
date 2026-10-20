@extends('layouts.admin')

@section('title', 'Scans')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Scans</h1>
        @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::CREATE_SCAN))
            <a href="{{ route('web-admin.scans.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Add Scan
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('web-admin.scans.index') }}" class="mb-6 flex gap-3">
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
                    <th class="px-6 py-3">Body Part</th>
                    <th class="px-6 py-3">Modality</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($scans as $scan)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $scan['name'] }}</td>
                        <td class="px-6 py-3 text-slate-600" dir="rtl">{{ $scan['name_ar'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $scan['code'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $scan['body_part'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $scan['modality'] ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @if ($scan['is_active'])
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_SINGLE_SCAN))
                                    <a href="{{ route('web-admin.scans.show', $scan['id']) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                @endif

                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_SCAN))
                                    <a href="{{ route('web-admin.scans.edit', $scan['id']) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                @endif

                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::DELETE_SCAN))
                                    <form
                                        method="POST"
                                        action="{{ route('web-admin.scans.destroy', $scan['id']) }}"
                                        onsubmit="return confirm('Delete this scan? Prescriptions that already use it are kept.');"
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
                        <td colspan="7" class="px-6 py-10 text-center text-slate-500">No scans found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($scans->hasPages())
        <div class="mt-6">
            {{ $scans->links() }}
        </div>
    @endif
@endsection
