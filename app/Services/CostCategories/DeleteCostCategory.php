<?php

namespace App\Services\CostCategories;

use App\Models\CostCategory;
use Illuminate\Validation\ValidationException;

class DeleteCostCategory
{
    /**
     * A category that still has costs cannot go, or those costs would lose
     * the category they were filed under.
     */
    public static function execute(CostCategory $category)
    {
        if ($category->costs()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'This category is used by existing costs and cannot be deleted.',
            ]);
        }

        return $category->delete();
    }
}
