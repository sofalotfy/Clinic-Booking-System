@extends('layouts.admin')

@section('title', 'Scan Details')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $scan['name'] }}</h1>
            @if ($scan['is_active'])
                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
            @else
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
            @endif
        </div>
        <div class="inline-flex items-center gap-3">
            @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_SCAN))
                <a href="{{ route('web-admin.scans.edit', $scan['id']) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Edit</a>
            @endif
            <a href="{{ $user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_SCANS) ? route('web-admin.scans.index') : route('web-admin.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back</a>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Name</dt>
                <dd class="font-medium text-slate-900">{{ $scan['name'] }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Arabic Name</dt>
                <dd class="font-medium text-slate-900" dir="rtl">{{ $scan['name_ar'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Code</dt>
                <dd class="font-medium text-slate-900">{{ $scan['code'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Body Part</dt>
                <dd class="font-medium text-slate-900">{{ $scan['body_part'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Modality</dt>
                <dd class="font-medium text-slate-900">{{ $scan['modality'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">With Contrast</dt>
                <dd class="font-medium text-slate-900">{{ $scan['with_contrast'] ? 'Yes' : 'No' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Created</dt>
                <dd class="font-medium text-slate-900">{{ $scan['created_at'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Updated</dt>
                <dd class="font-medium text-slate-900">{{ $scan['updated_at'] ?? '—' }}</dd>
            </div>
        </dl>

        @if ($scan['preparation'])
            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="mb-1 text-sm font-medium text-slate-700">Preparation</p>
                <p class="whitespace-pre-line text-sm text-slate-600">{{ $scan['preparation'] }}</p>
            </div>
        @endif

        @if ($scan['description'])
            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="mb-1 text-sm font-medium text-slate-700">Description</p>
                <p class="whitespace-pre-line text-sm text-slate-600">{{ $scan['description'] }}</p>
            </div>
        @endif
    </div>
@endsection
