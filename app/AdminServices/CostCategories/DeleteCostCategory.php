<?php

namespace App\AdminServices\CostCategories;

use App\Models\CostCategory;
use App\Services\CostCategories\DeleteCostCategory as DeleteService;

class DeleteCostCategory
{
    public static function execute(CostCategory $category)
    {
        return DeleteService::execute($category);
    }
}
