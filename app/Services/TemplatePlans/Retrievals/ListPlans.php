<?php

namespace App\Services\TemplatePlans\Retrievals;

use App\Models\TemplatePlan;

class ListPlans
{
    public static function execute($user)
    {
        return TemplatePlan::leftJoin('template_days', 'template_plans.id', 'template_days.template_plan_id')
            ->where('template_plans.doctor_id', $user->clinicDoctorId());
    }
}