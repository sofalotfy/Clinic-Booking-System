@extends('layouts.admin')

@section('title', 'Doctors')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Doctors</h1>
        <a href="{{ route('web-admin.doctors.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            Add Doctor
        </a>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Phone</th>
                    <th class="px-6 py-3">Gender</th>
                    <th class="px-6 py-3">Area</th>
                    <th class="px-6 py-3">Clinic</th>
                    <th class="px-6 py-3">Joined</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($doctors as $doctor)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $doctor['name'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['email'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['phone'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['gender'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['area'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['clinic_name'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $doctor['joined'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('web-admin.doctors.show', $doctor['id']) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                <a href="{{ route('web-admin.doctors.edit', $doctor['id']) }}" class="text-slate-600 hover:text-slate-900">Edit</a>

                                <form
                                    method="POST"
                                    action="{{ route('web-admin.doctors.destroy', $doctor['id']) }}"
                                    onsubmit="return confirm('Delete this doctor and all related data (appointments, clinic, whatsapp)?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-500">No doctors found.</td>
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