@extends('layouts.admin')

@section('title', 'Cost Category Details')

@section('content')
    @php($user = auth()->user())
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">{{ $category['name'] }}</h1>
        <div class="inline-flex items-center gap-3">
            @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_COST_CATEGORY))
                <a href="{{ route('web-admin.cost-categories.edit', $category['id']) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Edit</a>
            @endif
            <a href="{{ $user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_COST_CATEGORIES) ? route('web-admin.cost-categories.index') : route('web-admin.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back</a>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Name</dt>
                <dd class="font-medium text-slate-900">{{ $category['name'] }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Created</dt>
                <dd class="font-medium text-slate-900">{{ $category['created_at'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Updated</dt>
                <dd class="font-medium text-slate-900">{{ $category['updated_at'] ?? '—' }}</dd>
            </div>
        </dl>

        @if ($category['description'])
            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="mb-1 text-sm font-medium text-slate-700">Description</p>
                <p class="whitespace-pre-line text-sm text-slate-600" dir="auto">{{ $category['description'] }}</p>
            </div>
        @endif
    </div>
@endsection
