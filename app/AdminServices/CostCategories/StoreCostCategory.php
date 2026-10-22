<?php

namespace App\AdminServices\CostCategories;

use App\Models\CostCategory;
use App\Services\CostCategories\StoreCostCategory as StoreService;
use Illuminate\Validation\Rule;

class StoreCostCategory
{
    public static function execute($request)
    {
        return StoreService::execute($request->validate(self::rules()));
    }

    public static function rules(?CostCategory $category = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('cost_categories', 'name')->ignore($category?->id)],
            'description' => 'nullable|string',
        ];
    }
}
