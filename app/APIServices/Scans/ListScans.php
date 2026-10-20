<?php

namespace App\APIServices\Scans;

use App\Enums\ScanModality;
use App\Services\Scans\ListScans as ListService;
use Illuminate\Validation\Rule;

class ListScans
{
    public static function execute($request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'body_part' => 'nullable|string|max:255',
            'modality' => ['nullable', Rule::enum(ScanModality::class)],
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
            'scans.id',
            'scans.name',
            'scans.name_ar',
            'scans.code',
            'scans.body_part',
            'scans.modality',
            'scans.with_contrast',
            'scans.preparation',
            'scans.description',
        ];
    }
}
