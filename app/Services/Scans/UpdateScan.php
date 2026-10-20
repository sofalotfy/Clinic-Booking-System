<?php

namespace App\Services\Scans;

use App\Models\Scan;

class UpdateScan
{
    public static function execute(Scan $scan, array $data): Scan
    {
        $scan->update($data);

        return $scan->refresh();
    }
}
