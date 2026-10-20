@extends('layouts.admin')

@section('title', 'Lab Details')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $lab['name'] }}</h1>
            @if ($lab['is_active'])
                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
            @else
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
            @endif
        </div>
        <div class="inline-flex items-center gap-3">
            @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_LAB))
                <a href="{{ route('web-admin.labs.edit', $lab['id']) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Edit</a>
            @endif
            <a href="{{ $user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_LABS) ? route('web-admin.labs.index') : route('web-admin.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back</a>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Name</dt>
                <dd class="font-medium text-slate-900">{{ $lab['name'] }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Arabic Name</dt>
                <dd class="font-medium text-slate-900" dir="rtl">{{ $lab['name_ar'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Code</dt>
                <dd class="font-medium text-slate-900">{{ $lab['code'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Category</dt>
                <dd class="font-medium text-slate-900">{{ $lab['category'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Sample Type</dt>
                <dd class="font-medium text-slate-900">{{ $lab['sample_type'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Fasting Required</dt>
                <dd class="font-medium text-slate-900">{{ $lab['fasting_required'] ? 'Yes' : 'No' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Created</dt>
                <dd class="font-medium text-slate-900">{{ $lab['created_at'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Updated</dt>
                <dd class="font-medium text-slate-900">{{ $lab['updated_at'] ?? '—' }}</dd>
            </div>
        </dl>

        @if ($lab['preparation'])
            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="mb-1 text-sm font-medium text-slate-700">Preparation</p>
                <p class="whitespace-pre-line text-sm text-slate-600">{{ $lab['preparation'] }}</p>
            </div>
        @endif

        @if ($lab['description'])
            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="mb-1 text-sm font-medium text-slate-700">Description</p>
                <p class="whitespace-pre-line text-sm text-slate-600">{{ $lab['description'] }}</p>
            </div>
        @endif
    </div>
@endsection
