<?php

namespace App\AdminServices\CostCategories;

use App\Models\CostCategory;

class ShowCostCategory
{
    public static function execute(CostCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'created_at' => $category->created_at?->format('Y-m-d'),
            'updated_at' => $category->updated_at?->format('Y-m-d'),
        ];
    }
}
