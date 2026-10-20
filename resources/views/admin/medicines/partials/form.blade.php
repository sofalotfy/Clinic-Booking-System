@php
    $input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $label = 'mb-1 block text-sm font-medium text-slate-700';
    $optional = '<span class="font-normal text-slate-400">(optional)</span>';
    $editing = isset($medicine);
    $field = fn (string $key, $default = '') => old($key, $medicine[$key] ?? $default);
@endphp

<div class="grid grid-cols-1 gap-6">
    @if ($editing)
        <p class="rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Changes to fields that come from the sheet will be overwritten by the next sheet upload. The Active flag is kept.
        </p>
    @endif

    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Medicine</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="{{ $label }}">Trade Name (English)</label>
                <input id="name" type="text" name="name" value="{{ $field('name') }}" required class="{{ $input }}">
            </div>

            <div>
                <label for="name_ar" class="{{ $label }}">Trade Name (Arabic) {!! $optional !!}</label>
                <input id="name_ar" type="text" name="name_ar" dir="rtl" value="{{ $field('name_ar') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="active_ingredient" class="{{ $label }}">Active Ingredient {!! $optional !!}</label>
                <input id="active_ingredient" type="text" name="active_ingredient" value="{{ $field('active_ingredient') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="category" class="{{ $label }}">Category {!! $optional !!}</label>
                <input id="category" type="text" name="category" value="{{ $field('category') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="company" class="{{ $label }}">Company {!! $optional !!}</label>
                <input id="company" type="text" name="company" value="{{ $field('company') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="dosage_form" class="{{ $label }}">Dosage Form {!! $optional !!}</label>
                <input id="dosage_form" type="text" name="dosage_form" placeholder="e.g. tablet" value="{{ $field('dosage_form') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="strength" class="{{ $label }}">Strength {!! $optional !!}</label>
                <input id="strength" type="text" name="strength" placeholder="e.g. 500 mg" value="{{ $field('strength') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="size" class="{{ $label }}">Size {!! $optional !!}</label>
                <input id="size" type="text" name="size" value="{{ $field('size') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="origin" class="{{ $label }}">Type {!! $optional !!}</label>
                <input id="origin" type="text" name="origin" placeholder="e.g. imported / local" value="{{ $field('origin') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="barcode" class="{{ $label }}">Barcode {!! $optional !!}</label>
                <input id="barcode" type="text" name="barcode" value="{{ $field('barcode') }}" class="{{ $input }}">
            </div>

            <div class="sm:col-span-2">
                <label for="image_url" class="{{ $label }}">Image URL {!! $optional !!}</label>
                <input id="image_url" type="url" name="image_url" value="{{ $field('image_url') }}" class="{{ $input }}">
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Pricing & Packaging</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="price" class="{{ $label }}">Box Price (EGP) {!! $optional !!}</label>
                <input id="price" type="number" step="0.01" min="0" name="price" value="{{ $field('price') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="unit_price" class="{{ $label }}">Unit Price (EGP) {!! $optional !!}</label>
                <input id="unit_price" type="number" step="0.01" min="0" name="unit_price" value="{{ $field('unit_price') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="major_units" class="{{ $label }}">Major Units {!! $optional !!}</label>
                <input id="major_units" type="number" min="0" name="major_units" value="{{ $field('major_units') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="minor_units" class="{{ $label }}">Minor Units {!! $optional !!}</label>
                <input id="minor_units" type="number" min="0" name="minor_units" value="{{ $field('minor_units') }}" class="{{ $input }}">
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked($field('is_active', true)) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Active (available to clinics)
                </label>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            {{ $editing ? 'Save Changes' : 'Create Medicine' }}
        </button>
        <a href="{{ route('web-admin.medicines.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
    </div>
</div>
