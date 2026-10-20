@extends('layouts.admin')

@section('title', 'Medicine Details')

@section('content')
    @php($user = auth()->user())
    @php($rows = [
        'Trade Name (Arabic)' => $medicine['name_ar'],
        'Active Ingredient' => $medicine['active_ingredient'],
        'Category' => $medicine['category'],
        'Company' => $medicine['company'],
        'Dosage Form' => $medicine['dosage_form'],
        'Strength' => $medicine['strength'],
        'Size' => $medicine['size'],
        'Type' => $medicine['origin'],
        'Barcode' => $medicine['barcode'],
        'Box Price (EGP)' => $medicine['price'],
        'Unit Price (EGP)' => $medicine['unit_price'],
        'Major Units' => $medicine['major_units'],
        'Minor Units' => $medicine['minor_units'],
        'Added' => $medicine['created_at'],
        'Last Updated' => $medicine['updated_at'],
    ])
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $medicine['name'] }}</h1>
            @if ($medicine['is_active'])
                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
            @else
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
            @endif
        </div>
        <div class="inline-flex items-center gap-3">
            @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::UPDATE_MEDICINE))
                <a href="{{ route('web-admin.medicines.edit', $medicine['id']) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Edit</a>
            @endif
            <a href="{{ $user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_MEDICINES) ? route('web-admin.medicines.index') : route('web-admin.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
            <dl class="space-y-3 text-sm">
                @foreach ($rows as $label => $value)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="text-right font-medium text-slate-900" @if ($label === 'Trade Name (Arabic)') dir="rtl" @endif>{{ $value ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        @if ($medicine['image_url'])
            <div class="rounded-lg bg-white p-6 shadow">
                <img src="{{ $medicine['image_url'] }}" alt="{{ $medicine['name'] }}" loading="lazy" referrerpolicy="no-referrer" class="mx-auto max-h-72 object-contain">
            </div>
        @endif
    </div>
@endsection
