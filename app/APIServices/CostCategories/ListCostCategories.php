<?php

namespace App\APIServices\CostCategories;

use App\Services\CostCategories\ListCostCategories as ListService;

class ListCostCategories
{
    public static function execute($request)
    {
        return ListService::execute()
            ->get(['id', 'name', 'description']);
    }
}
