<?php

namespace App\AdminServices\Scans;

use App\Enums\ScanModality;
use App\Models\Scan;
use App\Services\Scans\StoreScan as StoreScanService;
use Illuminate\Validation\Rule;

class StoreScan
{
    public static function execute($request)
    {
        return StoreScanService::execute($request->validate(self::rules()));
    }

    public static function rules(?Scan $scan = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('scans', 'name')->withoutTrashed()->ignore($scan?->id)],
            'name_ar' => 'nullable|string|max:255',
            'code' => ['nullable', 'string', 'max:50', Rule::unique('scans', 'code')->withoutTrashed()->ignore($scan?->id)],
            'body_part' => 'nullable|string|max:255',
            'modality' => ['nullable', Rule::enum(ScanModality::class)],
            'with_contrast' => 'boolean',
            'preparation' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
