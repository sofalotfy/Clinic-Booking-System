<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\CostCategories\DeleteCostCategory;
use App\AdminServices\CostCategories\ListCostCategories;
use App\AdminServices\CostCategories\ShowCostCategory;
use App\AdminServices\CostCategories\StoreCostCategory;
use App\AdminServices\CostCategories\UpdateCostCategory;
use App\Enums\AdminPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\CostCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CostCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_ALL_COST_CATEGORIES->value, only: ['index']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_SINGLE_COST_CATEGORY->value, only: ['show']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::CREATE_COST_CATEGORY->value, only: ['create', 'store']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::UPDATE_COST_CATEGORY->value, only: ['edit', 'update']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::DELETE_COST_CATEGORY->value, only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        return view('admin.cost-categories.index', [
            'categories' => ListCostCategories::execute($request),
        ]);
    }

    public function create()
    {
        return view('admin.cost-categories.create');
    }

    public function store(Request $request)
    {
        StoreCostCategory::execute($request);

        return redirect()->route('web-admin.cost-categories.index')
            ->with('status', 'Cost category created successfully.');
    }

    public function show(CostCategory $costCategory)
    {
        return view('admin.cost-categories.show', [
            'category' => ShowCostCategory::execute($costCategory),
        ]);
    }

    public function edit(CostCategory $costCategory)
    {
        return view('admin.cost-categories.edit', [
            'category' => ShowCostCategory::execute($costCategory),
        ]);
    }

    public function update(Request $request, CostCategory $costCategory)
    {
        UpdateCostCategory::execute($request, $costCategory);

        return redirect()->route('web-admin.cost-categories.show', $costCategory->id)
            ->with('status', 'Cost category updated successfully.');
    }

    public function destroy(CostCategory $costCategory)
    {
        DeleteCostCategory::execute($costCategory);

        return redirect()->route('web-admin.cost-categories.index')
            ->with('status', 'Cost category deleted successfully.');
    }
}
