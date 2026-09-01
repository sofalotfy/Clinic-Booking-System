<?php

namespace App\APIServices\TemplatePlans;

use App\Services\TemplatePlans\Modifications\ActivatePlan as ActivateService;
use Carbon\Carbon;

class ActivatePlan
{
    public static function execute($request, $templatePlan)
    {
        $start_date = $request->input('start_date', Carbon::now()->addDays(1)->toDateString());

        return response()->json([
            'success' => ActivateService::execute($request->user(), $templatePlan, $start_date),
        ]);
    }
}