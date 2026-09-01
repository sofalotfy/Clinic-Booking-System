<?php

namespace App\APIServices\TemplatePlans;

use App\Services\TemplatePlans\Retrievals\CountColidingAppoinments;
use App\Services\TemplatePlans\Retrievals\CountTruncatedAppointments;
use App\Services\TemplatePlans\Retrievals\CountOverflowedAppointments;
use Carbon\Carbon;

class CheckRePlan
{
    public static function execute($request, $templatePlan)
    {
        $start_date = $request->input('start_date', Carbon::now()->addDays(1)->toDateString());

        return response()->json([
            'colliding_appointments' => count(CountColidingAppoinments::execute($templatePlan, $start_date)),
            'truncated_appointments' => count(CountTruncatedAppointments::execute($templatePlan, $start_date)),
            'overflowed_appointments' => count(CountOverflowedAppointments::execute($templatePlan, $start_date)),
        ]);
    }
}