@extends('layouts.admin')

@section('title', 'Doctor Details')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">{{ $doctor['name'] }}</h1>
        <div class="inline-flex items-center gap-3">
            <a href="{{ route('web-admin.doctors.edit', $doctor['id']) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Edit</a>
            <a href="{{ route('web-admin.doctors.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back</a>
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
                            @if ($doctor['clinic']['location_link'])
                                <a href="{{ $doctor['clinic']['location_link'] }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800">Open</a>
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
    </div>
@endsection