<?php

namespace App\AdminServices\Scans;

use App\Services\Scans\ListScans as ListScansService;

class ListScans
{
    public static function execute($request)
    {
        return ListScansService::execute($request->only(['search']))
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($scan) => ShowScan::execute($scan));
    }
}
