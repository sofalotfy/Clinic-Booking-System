<?php

namespace App\Http\Controllers\API;

use App\APIServices\Labs\ListLabs;
use App\APIServices\Labs\ShowLab;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LabController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_LABS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_LAB->value,
                only: ['show']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'labs' => ListLabs::execute($request),
        ]);
    }

    public function show(Lab $lab)
    {
        return response()->json([
            'success' => true,
            'lab' => ShowLab::execute($lab),
        ]);
    }
}
