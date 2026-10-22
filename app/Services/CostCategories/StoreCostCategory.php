<?php

namespace App\Services\CostCategories;

use App\Models\CostCategory;

class StoreCostCategory
{
    public static function execute(array $data): CostCategory
    {
        return CostCategory::create($data);
    }
}
