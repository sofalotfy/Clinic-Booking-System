<?php

namespace App\AdminServices\Scans;

use App\Models\Scan;
use App\Services\Scans\UpdateScan as UpdateScanService;

class UpdateScan
{
    public static function execute($request, Scan $scan)
    {
        return UpdateScanService::execute($scan, $request->validate(StoreScan::rules($scan)));
    }
}
