@extends('layouts.admin')

@section('title', 'Doctor Details')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if ($doctor['image_url'])
                <img src="{{ $doctor['image_url'] }}" alt="{{ $doctor['name'] }}" class="h-14 w-14 rounded-full object-cover">
            @else
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-200 text-lg font-semibold text-slate-500">{{ strtoupper(substr($doctor['name'] ?? '?', 0, 1)) }}</span>
            @endif
            <h1 class="text-2xl font-semibold text-slate-900">{{ $doctor['name'] }}</h1>
            @if ($doctor['suspended'])
                <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Suspended</span>
            @endif
        </div>
        <div class="inline-flex items-center gap-3">
            @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_DOCTOR))
                <a href="{{ route('web-admin.doctors.edit', $doctor['id']) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Edit</a>
            @endif
            <a href="{{ $user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_DOCTORS) ? route('web-admin.doctors.index') : route('web-admin.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back</a>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm font-medium text-slate-500">Appointments (last {{ $doctor['stats']['window_days'] }} days)</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $doctor['stats']['appointments_total'] }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($doctor['stats']['appointments_by_status'] as $status => $count)
                    @continue($count === 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ $status }}
                        <span class="font-semibold text-slate-900">{{ $count }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm font-medium text-slate-500">Appointments (next {{ $doctor['stats']['window_days'] }} days)</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $doctor['stats']['appointments_upcoming_total'] }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($doctor['stats']['appointments_upcoming_by_status'] as $status => $count)
                    @continue($count === 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ $status }}
                        <span class="font-semibold text-slate-900">{{ $count }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm font-medium text-slate-500">Patients</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $doctor['stats']['patients'] }}</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm font-medium text-slate-500">Blocked patients</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $doctor['stats']['blocked_patients'] }}</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm font-medium text-slate-500">Assistants</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $doctor['stats']['assistants'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Account</h2>

            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Name</dt>
                    <dd class="font-medium text-slate-900">{{ $doctor['name'] ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Email</dt>
                    <dd class="font-medium text-slate-900">{{ $doctor['email'] ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Phone</dt>
                    <dd class="font-medium text-slate-900">{{ $doctor['phone'] ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Age</dt>
                    <dd class="font-medium text-slate-900">{{ $doctor['age'] ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Gender</dt>
                    <dd class="font-medium text-slate-900">{{ $doctor['gender'] ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Area</dt>
                    <dd class="font-medium text-slate-900">{{ $doctor['area'] ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Joined</dt>
                    <dd class="font-medium text-slate-900">{{ $doctor['created_at'] ?? '—' }}</dd>
                </div>
            </dl>

            @if ($doctor['description'])
                <div class="mt-4 border-t border-slate-200 pt-4">
                    <p class="mb-1 text-sm font-medium text-slate-700">Description</p>
                    <p class="text-sm text-slate-600">{{ $doctor['description'] }}</p>
                </div>
            @endif
        </div>

        @if ($doctor['clinic'])
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Clinic</h2>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Name</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['clinic']['name'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['clinic']['clinic_phone'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Notifications Phone</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['clinic']['notifications_phone'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Address</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['clinic']['address'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Location</dt>
                        <dd class="font-medium text-slate-900">
                            @if ($doctor['clinic']['location_link_url'])
                                <a href="{{ $doctor['clinic']['location_link_url'] }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800">Open</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Facebook</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['clinic']['facebook'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Instagram</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['clinic']['instgram'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">LinkedIn</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['clinic']['linkedin'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Vezeeta</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['clinic']['vezeeta'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Created</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['clinic']['created_at'] ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        @endif

        @if ($doctor['whatsapp'])
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">WhatsApp</h2>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Phone Number ID</dt>
                        <dd class="font-medium text-slate-900">{{ $doctor['whatsapp']['phone_number_id'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Status</dt>
                        <dd class="font-medium text-slate-900">
                            @if ($doctor['whatsapp']['is_active'])
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        @endif

        @if (($doctor['suspended'] && $user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::REINSTATE_DOCTOR))
            || (! $doctor['suspended'] && $user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::SUSPEND_DOCTOR))
            || count($doctor['suspensions']) > 0)
            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Suspension</h2>

                @if ($doctor['suspended'])
                    @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::REINSTATE_DOCTOR))
                        <form
                            method="POST"
                            action="{{ route('web-admin.doctors.reinstate', $doctor['id']) }}"
                            onsubmit="return confirm('Reinstate this doctor? They and their assistants will be able to use the system again.');"
                            class="mb-6"
                        >
                            @csrf
                            <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Reinstate Doctor</button>
                        </form>
                    @endif
                @elseif ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::SUSPEND_DOCTOR))
                    <form
                        method="POST"
                        action="{{ route('web-admin.doctors.destroy', $doctor['id']) }}"
                        onsubmit="return confirm('Suspend this doctor? They, their assistants and their WhatsApp bot will be disabled until reinstated.');"
                        class="mb-6 space-y-3"
                    >
                        @csrf
                        @method('DELETE')
                        <label for="reason" class="block text-sm font-medium text-slate-700">Reason (optional)</label>
                        <textarea id="reason" name="reason" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Suspend Doctor</button>
                    </form>
                @endif

                @if (count($doctor['suspensions']) > 0)
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-2">Suspended</th>
                                <th class="px-4 py-2">By</th>
                                <th class="px-4 py-2">Reason</th>
                                <th class="px-4 py-2">Lifted</th>
                                <th class="px-4 py-2">By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($doctor['suspensions'] as $suspension)
                                <tr>
                                    <td class="px-4 py-2 text-slate-900">{{ $suspension['suspended_at'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-slate-600">{{ $suspension['suspended_by'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-slate-600">{{ $suspension['reason'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-slate-600">
                                        @if ($suspension['lifted_at'])
                                            {{ $suspension['lifted_at'] }}
                                        @else
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Active</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-slate-600">{{ $suspension['lifted_by'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-sm text-slate-500">This doctor has never been suspended.</p>
                @endif
            </div>
        @endif
    </div>
@endsection