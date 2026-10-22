<?php

namespace App\Http\Controllers\API;

use App\APIServices\Costs\DeleteCost;
use App\APIServices\Costs\ListCosts;
use App\APIServices\Costs\ShowCost;
use App\APIServices\Costs\StoreCost;
use App\APIServices\Costs\UpdateCost;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Cost;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CostController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_COSTS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_COST->value . ',cost',
                only: ['show']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_COST->value,
                only: ['store']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_COST->value . ',cost',
                only: ['update']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::DELETE_COST->value . ',cost',
                only: ['destroy']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'costs' => ListCosts::execute($request),
        ]);
    }

    public function show(Cost $cost)
    {
        return response()->json([
            'success' => true,
            'cost' => ShowCost::execute($cost),
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'success' => true,
            'cost' => StoreCost::execute($request),
        ], 201);
    }

    public function update(Request $request, Cost $cost)
    {
        return response()->json([
            'success' => true,
            'cost' => UpdateCost::execute($request, $cost),
        ]);
    }

    public function destroy(Cost $cost)
    {
        DeleteCost::execute($cost);

        return response()->json([
            'success' => true,
            'message' => 'Cost deleted successfully',
        ]);
    }
}
