<?php

namespace App\AdminServices\Labs;

use App\Models\Lab;

class ShowLab
{
    public static function execute(Lab $lab): array
    {
        return [
            'id' => $lab->id,
            'name' => $lab->name,
            'name_ar' => $lab->name_ar,
            'code' => $lab->code,
            'category' => $lab->category,
            'sample_type' => $lab->sample_type?->value,
            'fasting_required' => $lab->fasting_required,
            'preparation' => $lab->preparation,
            'description' => $lab->description,
            'is_active' => $lab->is_active,
            'created_at' => $lab->created_at?->format('Y-m-d'),
            'updated_at' => $lab->updated_at?->format('Y-m-d'),
        ];
    }
}
