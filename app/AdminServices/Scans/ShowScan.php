<?php

namespace App\AdminServices\Scans;

use App\Models\Scan;

class ShowScan
{
    public static function execute(Scan $scan): array
    {
        return [
            'id' => $scan->id,
            'name' => $scan->name,
            'name_ar' => $scan->name_ar,
            'code' => $scan->code,
            'body_part' => $scan->body_part,
            'modality' => $scan->modality?->value,
            'with_contrast' => $scan->with_contrast,
            'preparation' => $scan->preparation,
            'description' => $scan->description,
            'is_active' => $scan->is_active,
            'created_at' => $scan->created_at?->format('Y-m-d'),
            'updated_at' => $scan->updated_at?->format('Y-m-d'),
        ];
    }
}
