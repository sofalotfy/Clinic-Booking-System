<?php

namespace App\APIServices\Costs;

use App\Services\Costs\StoreCost as StoreService;

class StoreCost
{
    public static function execute($request): array
    {
        $cost = StoreService::execute($request->user(), $request->validate(CostRules::rules()));

        return ShowCost::execute($cost);
    }
}
