<?php

namespace App\Http\Controllers\API;

use App\APIServices\CostCategories\ListCostCategories;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CostCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_COST_CATEGORIES->value,
                only: ['index']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'categories' => ListCostCategories::execute($request),
        ]);
    }
}
