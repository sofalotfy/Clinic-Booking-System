<?php

namespace App\APIServices\Costs;

use App\Models\Cost;
use App\Services\Costs\UpdateCost as UpdateService;

class UpdateCost
{
    public static function execute($request, Cost $cost): array
    {
        $cost = UpdateService::execute($cost, $request->validate(CostRules::rules(partial: true)));

        return ShowCost::execute($cost);
    }
}
