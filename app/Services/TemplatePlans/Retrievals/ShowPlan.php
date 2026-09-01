<?php

namespace App\Services\TemplatePlans\Retrievals;

use App\Models\TemplatePlan;

class ShowPlan
{
    public static function execute($user, $plan)
    {
        return TemplatePlan::leftJoin('template_days', 'template_plans.id', 'template_days.template_plan_id')
            ->where('template_plans.doctor_id', $user->clinicDoctorId())
            ->where('template_plans.id', $plan->id);
    }
}