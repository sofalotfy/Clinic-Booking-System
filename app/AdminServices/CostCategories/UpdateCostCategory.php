<?php

namespace App\AdminServices\CostCategories;

use App\Models\CostCategory;
use App\Services\CostCategories\UpdateCostCategory as UpdateService;

class UpdateCostCategory
{
    public static function execute($request, CostCategory $category)
    {
        return UpdateService::execute($category, $request->validate(StoreCostCategory::rules($category)));
    }
}
