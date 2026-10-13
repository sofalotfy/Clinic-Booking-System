@php
    $input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $label = 'mb-1 block text-sm font-medium text-slate-700';
    $editing = isset($role);
    $selectedIds = array_map('intval', (array) old('permissions', $role['permission_ids'] ?? []));
    $grouped = collect($permissions)->groupBy('group');
@endphp

<div class="grid grid-cols-1 gap-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Role</h2>

        <div>
            <label for="name" class="{{ $label }}">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $role['name'] ?? '') }}" required class="{{ $input }}">
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Permissions</h2>

        <div class="space-y-4">
            @foreach ($grouped as $group => $groupPermissions)
                <div class="rounded-lg border border-slate-200" data-permission-group>
                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-2">
                        <h3 class="text-sm font-semibold text-slate-800">{{ $group }}</h3>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-slate-600">
                            <input
                                type="checkbox"
                                data-toggle-group
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >
                            Select all
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($groupPermissions as $permission)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission['id'] }}"
                                    data-group-item
                                    @checked(in_array($permission['id'], $selectedIds))
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                >
                                {{ $permission['name'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            {{ $editing ? 'Save Changes' : 'Create Role' }}
        </button>
        <a href="{{ route('web-admin.roles.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
    </div>
</div>

<script>
    document.querySelectorAll('[data-permission-group]').forEach(function (group) {
        var toggle = group.querySelector('[data-toggle-group]');
        var items = Array.prototype.slice.call(group.querySelectorAll('[data-group-item]'));

        function refresh() {
            var checked = items.filter(function (item) { return item.checked; }).length;
            toggle.checked = items.length > 0 && checked === items.length;
            toggle.indeterminate = checked > 0 && checked < items.length;
        }

        toggle.addEventListener('change', function () {
            items.forEach(function (item) { item.checked = toggle.checked; });
            toggle.indeterminate = false;
        });

        items.forEach(function (item) {
            item.addEventListener('change', refresh);
        });

        refresh();
    });
</script>
