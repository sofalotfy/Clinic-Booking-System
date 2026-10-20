<?php

namespace App\APIServices\Labs;

use App\Models\Lab;

class ShowLab
{
    public static function execute(Lab $lab)
    {
        return $lab->only([
            'id',
            'name',
            'name_ar',
            'code',
            'category',
            'sample_type',
            'fasting_required',
            'preparation',
            'description',
            'is_active',
        ]);
    }
}
