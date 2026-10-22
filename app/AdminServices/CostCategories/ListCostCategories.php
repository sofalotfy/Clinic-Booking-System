<?php

namespace App\AdminServices\CostCategories;

use App\Services\CostCategories\ListCostCategories as ListService;

class ListCostCategories
{
    public static function execute($request)
    {
        return ListService::execute($request->only(['search']))
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($category) => ShowCostCategory::execute($category));
    }
}
