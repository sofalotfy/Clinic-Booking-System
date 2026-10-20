@extends('layouts.admin')

@section('title', 'Medicines')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Medicines</h1>
        <div class="inline-flex items-center gap-3">
            @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::IMPORT_MEDICINES))
                <a href="{{ route('web-admin.medicines.import') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Upload Sheet
                </a>
            @endif
            @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::CREATE_MEDICINE))
                <a href="{{ route('web-admin.medicines.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Add Medicine
                </a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('web-admin.medicines.index') }}" class="mb-6 flex gap-3">
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search by trade name, Arabic name, active ingredient or barcode"
            class="w-full max-w-lg rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
        >
        <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Search</button>
    </form>

    <p class="mb-3 text-sm text-slate-500">{{ number_format($medicines->total()) }} medicines</p>

    <div class="overflow-x-auto rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-3">Trade Name</th>
                    <th class="px-6 py-3">Arabic Name</th>
                    <th class="px-6 py-3">Active Ingredient</th>
                    <th class="px-6 py-3">Form</th>
                    <th class="px-6 py-3 text-right">Price (EGP)</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($medicines as $medicine)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $medicine['name'] }}</td>
                        <td class="px-6 py-3 text-slate-600" dir="rtl">{{ $medicine['name_ar'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $medicine['active_ingredient'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $medicine['dosage_form'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-right text-slate-600">{{ $medicine['price'] ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @if ($medicine['is_active'])
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_SINGLE_MEDICINE))
                                    <a href="{{ route('web-admin.medicines.show', $medicine['id']) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                @endif

                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_MEDICINE))
                                    <a href="{{ route('web-admin.medicines.edit', $medicine['id']) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                @endif

                                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::DELETE_MEDICINE))
                                    <form
                                        method="POST"
                                        action="{{ route('web-admin.medicines.destroy', $medicine['id']) }}"
                                        onsubmit="return confirm('Delete this medicine? Prescriptions that already use it are kept, and later sheet uploads will not bring it back.');"
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
                        <td colspan="7" class="px-6 py-10 text-center text-slate-500">No medicines found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($medicines->hasPages())
        <div class="mt-6">
            {{ $medicines->links() }}
        </div>
    @endif
@endsection
