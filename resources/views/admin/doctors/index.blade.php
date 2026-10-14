@extends('layouts.admin')

@section('title', 'Doctors')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Doctors</h1>
        @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::CREATE_DOCTOR))
            <a href="{{ route('web-admin.doctors.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Add Doctor
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
                    <th class="px-6 py-3">Clinic</th>
                    <th class="px-6 py-3">Joined</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($doctors as $doctor)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $doctor['name'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['email'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['phone'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['clinic_name'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['joined'] ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @if ($doctor['suspended'])
                                <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Suspended</span>
                            @else
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_SINGLE_DOCTOR))
                                    <a href="{{ route('web-admin.doctors.show', $doctor['id']) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                @endif

                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_DOCTOR))
                                    <a href="{{ route('web-admin.doctors.edit', $doctor['id']) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                @endif

                                @if ($doctor['suspended'])
                                    @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::REINSTATE_DOCTOR))
                                        <form
                                            method="POST"
                                            action="{{ route('web-admin.doctors.reinstate', $doctor['id']) }}"
                                            onsubmit="return confirm('Reinstate this doctor? They and their assistants will be able to use the system again.');"
                                        >
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-800">Reinstate</button>
                                        </form>
                                    @endif
                                @elseif ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::SUSPEND_DOCTOR))
                                    <form
                                        method="POST"
                                        action="{{ route('web-admin.doctors.destroy', $doctor['id']) }}"
                                        onsubmit="return confirm('Suspend this doctor? They, their assistants and their WhatsApp bot will be disabled until reinstated.');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Suspend</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-500">No doctors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($doctors->hasPages())
        <div class="mt-6">
            {{ $doctors->links() }}
        </div>
    @endif
@endsection