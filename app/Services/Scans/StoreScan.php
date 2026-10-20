<?php

namespace App\Services\Scans;

use App\Models\Scan;

class StoreScan
{
    public static function execute(array $data): Scan
    {
        return Scan::create($data);
    }
}
