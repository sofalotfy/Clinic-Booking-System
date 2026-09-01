<?php

namespace App\APIServices\TemplatePlans;

use App\Services\TemplatePlans\Modifications\DeletePlan as DeleteService;

class DeletePlan
{
    public static function execute($request, $plan)
    {
        return response()->json([
            "success" => DeleteService::execute($request, $plan),
        ]);
    }
}