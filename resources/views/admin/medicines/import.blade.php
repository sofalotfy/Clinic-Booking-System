@extends('layouts.admin')

@section('title', 'Upload Medicines Sheet')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Upload Medicines Sheet</h1>
        @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_MEDICINES))
            <a href="{{ route('web-admin.medicines.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to Medicines</a>
        @endif
    </div>

    <div class="mb-6 rounded-lg bg-white p-6 shadow">
        <form
            method="POST"
            action="{{ route('web-admin.medicines.import.store') }}"
            enctype="multipart/form-data"
            class="space-y-4"
            onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Syncing, this can take a minute…';"
        >
            @csrf
            <div>
                <label for="file" class="mb-1 block text-sm font-medium text-slate-700">Drug price sheet (.xlsx or .csv)</label>
                <input id="file" type="file" name="file" accept=".xlsx,.csv" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <ul class="list-disc space-y-1 pl-5 text-sm text-slate-600">
                <li>Only the first sheet is read. The first row must hold the column headers, in any order.</li>
                <li>Rows are matched to existing medicines by the English trade name (<span dir="rtl">الاسم التجاري بالإنجليزية</span>), ignoring case and extra spaces.</li>
                <li>Matching medicines are updated, new ones are added, and medicines missing from the sheet are left as they are.</li>
                <li>The Active flag and deleted medicines are kept as set in the admin panel.</li>
            </ul>

            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60">Upload and Sync</button>
        </form>
    </div>

    <h2 class="mb-4 text-lg font-semibold text-slate-900">Upload History</h2>

    <div class="overflow-x-auto rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-3">Uploaded</th>
                    <th class="px-6 py-3">File</th>
                    <th class="px-6 py-3">By</th>
                    <th class="px-6 py-3 text-right">Rows</th>
                    <th class="px-6 py-3 text-right">Added</th>
                    <th class="px-6 py-3 text-right">Updated</th>
                    <th class="px-6 py-3 text-right">Skipped</th>
                    <th class="px-6 py-3 text-right">Duplicates</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($imports as $import)
                    <tr class="align-top hover:bg-slate-50">
                        <td class="px-6 py-3 text-slate-600">{{ $import['created_at'] }}</td>
                        <td class="px-6 py-3 font-medium text-slate-900">
                            {{ $import['file_name'] }}
                            @if (count($import['errors']) > 0)
                                <details class="mt-1 font-normal">
                                    <summary class="cursor-pointer text-xs text-amber-700">{{ count($import['errors']) }} row notes</summary>
                                    <ul class="mt-1 space-y-0.5 text-xs text-slate-600">
                                        @foreach ($import['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-slate-600">{{ $import['uploaded_by'] ?? '—' }}</td>
                        <td class="px-6 py-3 text-right text-slate-600">{{ number_format($import['total_rows']) }}</td>
                        <td class="px-6 py-3 text-right text-green-700">{{ number_format($import['created_count']) }}</td>
                        <td class="px-6 py-3 text-right text-blue-700">{{ number_format($import['updated_count']) }}</td>
                        <td class="px-6 py-3 text-right text-slate-600">{{ number_format($import['skipped_count']) }}</td>
                        <td class="px-6 py-3 text-right text-slate-600">{{ number_format($import['duplicate_count']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-500">No sheets uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($imports->hasPages())
        <div class="mt-6">
            {{ $imports->links() }}
        </div>
    @endif
@endsection
