@php
    $input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $label = 'mb-1 block text-sm font-medium text-slate-700';
    $optional = '<span class="font-normal text-slate-400">(optional)</span>';
    $editing = isset($admin);
    $editingSelf = $editingSelf ?? false;
    $selectedRoleIds = array_map('intval', (array) old('roles', $admin['role_ids'] ?? []));
@endphp

<div class="grid grid-cols-1 gap-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Account</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="{{ $label }}">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $admin['name'] ?? '') }}" required class="{{ $input }}">
            </div>

            <div>
                <label for="email" class="{{ $label }}">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $admin['email'] ?? '') }}" required class="{{ $input }}">
            </div>

            <div>
                <label for="phone" class="{{ $label }}">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $admin['phone'] ?? '') }}" required class="{{ $input }}">
            </div>

            <div>
                <label for="password" class="{{ $label }}">{{ $editing ? 'New Password (leave blank to keep)' : 'Password' }}</label>
                <input id="password" type="password" name="password" {{ $editing ? '' : 'required' }} class="{{ $input }}">
            </div>

            <div>
                <label for="age" class="{{ $label }}">Age {!! $optional !!}</label>
                <input id="age" type="number" name="age" min="0" max="150" value="{{ old('age', $admin['age'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="gender" class="{{ $label }}">Gender {!! $optional !!}</label>
                <select id="gender" name="gender" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach (\App\Enums\Gender::cases() as $gender)
                        <option value="{{ $gender->value }}" @selected(old('gender', $admin['gender'] ?? '') == $gender->value)>{{ $gender->value }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="area" class="{{ $label }}">Area {!! $optional !!}</label>
                <input id="area" type="text" name="area" value="{{ old('area', $admin['area'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="image" class="{{ $label }}">Image {!! $optional !!}</label>
                <input id="image" type="file" name="image" accept="image/*" class="{{ $input }}">
            </div>

            <div class="sm:col-span-2">
                <span class="{{ $label }}">Roles</span>
                @if ($editingSelf)
                    <p class="mb-2 text-xs text-slate-500">You cannot change your own roles.</p>
                @endif
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($roles as $role)
                        <label class="flex items-center gap-2 text-sm {{ $editingSelf ? 'text-slate-500' : 'text-slate-700' }}">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role['id'] }}"
                                @checked(in_array($role['id'], $selectedRoleIds))
                                @disabled($editingSelf)
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >
                            {{ $role['name'] }}
                        </label>
                    @empty
                        <p class="text-sm text-slate-500">No roles available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            {{ $editing ? 'Save Changes' : 'Create Admin' }}
        </button>
        <a href="{{ route('web-admin.admins.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
    </div>
</div>
