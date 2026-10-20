<?php

namespace App\AdminServices\Scans;

use App\Models\Scan;
use App\Services\Scans\DeleteScan as DeleteScanService;

class DeleteScan
{
    public static function execute(Scan $scan)
    {
        return DeleteScanService::execute($scan);
    }
}
