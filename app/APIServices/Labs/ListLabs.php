<?php

namespace App\APIServices\Labs;

use App\Enums\LabSampleType;
use App\Services\Labs\ListLabs as ListService;
use Illuminate\Validation\Rule;

class ListLabs
{
    public static function execute($request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'sample_type' => ['nullable', Rule::enum(LabSampleType::class)],
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // Clinics only ever pick from the active catalog
        return ListService::execute([...$filters, 'is_active' => true])
            ->select(self::getSelects())
            ->paginate($filters['per_page'] ?? 50);
    }

    public static function getSelects(): array
    {
        return [
            'labs.id',
            'labs.name',
            'labs.name_ar',
            'labs.code',
            'labs.category',
            'labs.sample_type',
            'labs.fasting_required',
            'labs.preparation',
            'labs.description',
        ];
    }
}
