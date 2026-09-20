<?php

namespace App\Services\Doctors;

use App\Models\Doctor;
use App\Models\TemplatePlan;
use App\Enums\TemplatePlanStatus;

class GetActivePlan
{
    public static function execute(Doctor $doctor)
    {
        return $doctor->templatePlans()
            ->where('status', TemplatePlanStatus::ACTIVE)
            ->with('templateDays')
            ->first();
    }
}