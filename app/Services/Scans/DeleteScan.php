<?php

namespace App\Services\Scans;

use App\Models\Scan;

class DeleteScan
{
    /**
     * Soft deletes, so prescriptions that already reference the scan keep it.
     */
    public static function execute(Scan $scan)
    {
        return $scan->delete();
    }
}
