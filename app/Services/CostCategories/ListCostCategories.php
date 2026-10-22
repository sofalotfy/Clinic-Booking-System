<?php

namespace App\Services\CostCategories;

use App\Models\CostCategory;

class ListCostCategories
{
    public static function execute(array $filters = [])
    {
        return CostCategory::query()
            ->orderBy('name')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"));
    }
}
