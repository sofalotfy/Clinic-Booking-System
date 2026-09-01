<?php

namespace App\APIServices\TemplatePlans;

use App\Services\TemplatePlans\Creations\StorePlan as StoreService;

class StorePlan
{
    public static function execute($request)
    {
        $plan = StoreService::execute(
            $request->user(),
            $request->name,
            $request->description,
            $request->days,
        );

        return response()->json([
            "plan" => ShowPlan::execute($request, $plan),
        ]);
    }
}