<?php

namespace App\APIServices\Scans;

use App\Models\Scan;

class ShowScan
{
    public static function execute(Scan $scan)
    {
        return $scan->only([
            'id',
            'name',
            'name_ar',
            'code',
            'body_part',
            'modality',
            'with_contrast',
            'preparation',
            'description',
            'is_active',
        ]);
    }
}
