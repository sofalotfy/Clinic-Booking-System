@php
    $input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $label = 'mb-1 block text-sm font-medium text-slate-700';
    $editing = isset($doctor);
@endphp

<div class="grid grid-cols-1 gap-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Account</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="{{ $label }}">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $doctor['name'] ?? '') }}" required class="{{ $input }}">
            </div>

            <div>
                <label for="email" class="{{ $label }}">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $doctor['email'] ?? '') }}" required class="{{ $input }}">
            </div>

            <div>
                <label for="phone" class="{{ $label }}">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $doctor['phone'] ?? '') }}" required class="{{ $input }}">
            </div>

            <div>
                <label for="password" class="{{ $label }}">{{ $editing ? 'New Password (leave blank to keep)' : 'Password' }}</label>
                <input id="password" type="password" name="password" {{ $editing ? '' : 'required' }} class="{{ $input }}">
            </div>

            <div>
                <label for="age" class="{{ $label }}">Age</label>
                <input id="age" type="number" name="age" min="0" max="150" value="{{ old('age', $doctor['age'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="gender" class="{{ $label }}">Gender</label>
                <select id="gender" name="gender" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach (\App\Enums\Gender::cases() as $gender)
                        <option value="{{ $gender->value }}" @selected(old('gender', $doctor['gender'] ?? '') == $gender->value)>{{ $gender->value }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="area" class="{{ $label }}">Area</label>
                <input id="area" type="text" name="area" value="{{ old('area', $doctor['area'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="image" class="{{ $label }}">Image</label>
                <input id="image" type="file" name="image" accept="image/*" class="{{ $input }}">
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="{{ $label }}">Description</label>
                <textarea id="description" name="description" rows="4" class="{{ $input }}">{{ old('description', $doctor['description'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Clinic</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="clinic_name" class="{{ $label }}">Clinic Name</label>
                <input id="clinic_name" type="text" name="clinic_name" value="{{ old('clinic_name', $doctor['clinic']['name'] ?? '') }}" {{ $editing ? '' : 'required' }} class="{{ $input }}">
            </div>

            <div>
                <label for="clinic_phone" class="{{ $label }}">Clinic Phone</label>
                <input id="clinic_phone" type="text" name="clinic_phone" value="{{ old('clinic_phone', $doctor['clinic']['clinic_phone'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="notifications_phone" class="{{ $label }}">Notifications Phone</label>
                <input id="notifications_phone" type="text" name="notifications_phone" value="{{ old('notifications_phone', $doctor['clinic']['notifications_phone'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="clinic_location_link" class="{{ $label }}">Location Link</label>
                <input id="clinic_location_link" type="text" name="clinic_location_link" value="{{ old('clinic_location_link', $doctor['clinic']['location_link'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="clinic_address" class="{{ $label }}">Address</label>
                <input id="clinic_address" type="text" name="clinic_address" value="{{ old('clinic_address', $doctor['clinic']['address'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="clinic_facebook" class="{{ $label }}">Facebook</label>
                <input id="clinic_facebook" type="text" name="clinic_facebook" value="{{ old('clinic_facebook', $doctor['clinic']['facebook'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="clinic_instgram" class="{{ $label }}">Instagram</label>
                <input id="clinic_instgram" type="text" name="clinic_instgram" value="{{ old('clinic_instgram', $doctor['clinic']['instgram'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="clinic_linkedin" class="{{ $label }}">LinkedIn</label>
                <input id="clinic_linkedin" type="text" name="clinic_linkedin" value="{{ old('clinic_linkedin', $doctor['clinic']['linkedin'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="clinic_vezeeta" class="{{ $label }}">Vezeeta</label>
                <input id="clinic_vezeeta" type="text" name="clinic_vezeeta" value="{{ old('clinic_vezeeta', $doctor['clinic']['vezeeta'] ?? '') }}" class="{{ $input }}">
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">WhatsApp</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="whatsapp_phone_number_id" class="{{ $label }}">Phone Number ID</label>
                <input
                    id="whatsapp_phone_number_id"
                    type="text"
                    name="whatsapp_phone_number_id"
                    value="{{ old('whatsapp_phone_number_id', $doctor['whatsapp']['phone_number_id'] ?? '') }}"
                    {{ $editing ? '' : 'required' }}
                    class="{{ $input }}"
                >
                @if ($editing && ($doctor['whatsapp']['phone_number_id'] ?? null))
                    <p class="mt-1 text-xs text-slate-500">Change to update, or leave blank to keep.</p>
                @endif
            </div>

            <div>
                <label for="whatsapp_access_token" class="{{ $label }}">Access Token</label>
                <input
                    id="whatsapp_access_token"
                    type="password"
                    name="whatsapp_access_token"
                    {{ $editing ? '' : 'required' }}
                    class="{{ $input }}"
                >
                @if ($editing)
                    <p class="mt-1 text-xs text-slate-500">Leave blank to keep the current token.</p>
                @endif
            </div>

            @if ($editing)
                <div class="flex items-center gap-2 sm:col-span-2">
                    <input type="hidden" name="whatsapp_is_active" value="0">
                    <input
                        id="whatsapp_is_active"
                        type="checkbox"
                        name="whatsapp_is_active"
                        value="1"
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        @checked($doctor['whatsapp']['is_active'] ?? true)
                    >
                    <label for="whatsapp_is_active" class="text-sm text-slate-600">WhatsApp account is active</label>
                </div>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            {{ $editing ? 'Save Changes' : 'Create Doctor' }}
        </button>
        <a href="{{ $editing ? route('web-admin.doctors.index') : route('web-admin.doctors.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
    </div>
</div>