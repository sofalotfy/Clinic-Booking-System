@php
    $input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $label = 'mb-1 block text-sm font-medium text-slate-700';
    $optional = '<span class="font-normal text-slate-400">(optional)</span>';
    $editing = isset($category);
@endphp

<div class="grid grid-cols-1 gap-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Cost Category</h2>

        <div class="grid grid-cols-1 gap-4">
            <div>
                <label for="name" class="{{ $label }}">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $category['name'] ?? '') }}" required class="{{ $input }}">
            </div>

            <div>
                <label for="description" class="{{ $label }}">Description {!! $optional !!}</label>
                <textarea id="description" name="description" rows="3" dir="auto" class="{{ $input }}">{{ old('description', $category['description'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            {{ $editing ? 'Save Changes' : 'Create Category' }}
        </button>
        <a href="{{ route('web-admin.cost-categories.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
    </div>
</div>
