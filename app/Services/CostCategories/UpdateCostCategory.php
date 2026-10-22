<?php

namespace App\Services\CostCategories;

use App\Models\CostCategory;

class UpdateCostCategory
{
    public static function execute(CostCategory $category, array $data): CostCategory
    {
        $category->update($data);

        return $category->refresh();
    }
}
