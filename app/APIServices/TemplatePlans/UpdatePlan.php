<?php

namespace App\APIServices\TemplatePlans;

use App\Services\TemplatePlans\Modifications\UpdatePlan as UpdateService;

class UpdatePlan
{
    public static function execute($request,$templatePlan)
    {
        $plan = UpdateService::execute(
            $request->user(),
            $templatePlan,
            $request->name,
            $request->description,
            $request->days,
        );

        return response()->json([
            "plan" => ShowPlan::execute($request, $plan),
        ]);
    }
}