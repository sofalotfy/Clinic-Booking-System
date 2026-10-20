@php
    $input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $label = 'mb-1 block text-sm font-medium text-slate-700';
    $optional = '<span class="font-normal text-slate-400">(optional)</span>';
    $editing = isset($scan);
@endphp

<div class="grid grid-cols-1 gap-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Scan</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="{{ $label }}">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $scan['name'] ?? '') }}" required class="{{ $input }}">
            </div>

            <div>
                <label for="name_ar" class="{{ $label }}">Arabic Name {!! $optional !!}</label>
                <input id="name_ar" type="text" name="name_ar" dir="rtl" value="{{ old('name_ar', $scan['name_ar'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="code" class="{{ $label }}">Code {!! $optional !!}</label>
                <input id="code" type="text" name="code" value="{{ old('code', $scan['code'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="body_part" class="{{ $label }}">Body Part {!! $optional !!}</label>
                <input id="body_part" type="text" name="body_part" placeholder="e.g. Chest" value="{{ old('body_part', $scan['body_part'] ?? '') }}" class="{{ $input }}">
            </div>

            <div>
                <label for="modality" class="{{ $label }}">Modality {!! $optional !!}</label>
                <select id="modality" name="modality" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach (\App\Enums\ScanModality::cases() as $option)
                        <option value="{{ $option->value }}" @selected(old('modality', $scan['modality'] ?? '') == $option->value)>{{ $option->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col justify-end gap-2">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="with_contrast" value="0">
                    <input type="checkbox" name="with_contrast" value="1" @checked(old('with_contrast', $scan['with_contrast'] ?? false)) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    With contrast
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $scan['is_active'] ?? true)) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Active (available to clinics)
                </label>
            </div>

            <div class="sm:col-span-2">
                <label for="preparation" class="{{ $label }}">Preparation {!! $optional !!}</label>
                <textarea id="preparation" name="preparation" rows="2" class="{{ $input }}">{{ old('preparation', $scan['preparation'] ?? '') }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="{{ $label }}">Description {!! $optional !!}</label>
                <textarea id="description" name="description" rows="3" class="{{ $input }}">{{ old('description', $scan['description'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            {{ $editing ? 'Save Changes' : 'Create Scan' }}
        </button>
        <a href="{{ route('web-admin.scans.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
    </div>
</div>
