<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class CountTruncatedAppointments
{
    public static function execute(TemplatePlan $template, $start_date)
    {
        $end_date = Carbon::parse($start_date)->addDays(30)->toDateString();
        $includedDaysOfWeek = $template->templateDays->pluck('day_of_week')->toArray();

        $appointments = Appointment::where('doctor_id', $template->doctor_id)
            ->whereBetween('date', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])
            ->whereIn('status', [AppointmentStatus::ACTIVE, AppointmentStatus::QUEUED, AppointmentStatus::PENDING])
            ->orderBy('date')
            ->get();

        $truncated = $appointments->filter(function ($appointment) use ($includedDaysOfWeek) {
            $dayOfWeek = Carbon::parse($appointment->date)->dayOfWeek;
            return !in_array($dayOfWeek, $includedDaysOfWeek);
        });

        return $truncated->values();
    }
}