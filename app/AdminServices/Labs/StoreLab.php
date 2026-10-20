<?php

namespace App\AdminServices\Labs;

use App\Enums\LabSampleType;
use App\Models\Lab;
use App\Services\Labs\StoreLab as StoreLabService;
use Illuminate\Validation\Rule;

class StoreLab
{
    public static function execute($request)
    {
        return StoreLabService::execute($request->validate(self::rules()));
    }

    public static function rules(?Lab $lab = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('labs', 'name')->withoutTrashed()->ignore($lab?->id)],
            'name_ar' => 'nullable|string|max:255',
            'code' => ['nullable', 'string', 'max:50', Rule::unique('labs', 'code')->withoutTrashed()->ignore($lab?->id)],
            'category' => 'nullable|string|max:255',
            'sample_type' => ['nullable', Rule::enum(LabSampleType::class)],
            'fasting_required' => 'boolean',
            'preparation' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
