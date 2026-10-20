<?php

namespace App\Http\Controllers\API;

use App\APIServices\Scans\ListScans;
use App\APIServices\Scans\ShowScan;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ScanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_SCANS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_SCAN->value,
                only: ['show']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'scans' => ListScans::execute($request),
        ]);
    }

    public function show(Scan $scan)
    {
        return response()->json([
            'success' => true,
            'scan' => ShowScan::execute($scan),
        ]);
    }
}
