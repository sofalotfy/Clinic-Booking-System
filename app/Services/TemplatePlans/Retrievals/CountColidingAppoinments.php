<?php

namespace App\Services\TemplatePlans\Retrievals;

use App\Models\TemplatePlan;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class CountColidingAppoinments
{
    public static function execute(TemplatePlan $template, $start_date)
    {
        $end_date = Carbon::parse($start_date)->addDays(30)->toDateString();
        $includedDaysOfWeek = $template->templateDays->pluck('day_of_week')->toArray();

        $appointments = Appointment::where('doctor_id', $template->doctor_id)
            ->whereBetween('date', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])
            ->whereIn('status', AppointmentStatus::working())
            ->orderBy('date')
            ->get();

        $appointmentsOnIncludedDays = $appointments->filter(function ($appointment) use ($includedDaysOfWeek) {
            $dayOfWeek = Carbon::parse($appointment->date)->dayOfWeek;
            return in_array($dayOfWeek, $includedDaysOfWeek);
        });

        $groupedByDate = $appointmentsOnIncludedDays->groupBy(function ($appointment) {
            return Carbon::parse($appointment->date)->toDateString();
        });

        $colliding = collect();

        foreach ($groupedByDate as $date => $dateAppointments) {
            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
            $templateDay = $template->templateDays->firstWhere('day_of_week', $dayOfWeek);

            if (!$templateDay) {
                continue;
            }

            // Calculate slots count for this day
            $start = Carbon::parse($date . ' ' . $templateDay->start_time);
            $end = Carbon::parse($date . ' ' . $templateDay->end_time);
            $slotCount = 0;
            $current = $start->copy();
            $duration = (int) $templateDay->appointment_duration;

            while ($current->copy()->addMinutes($duration)->lte($end)) {
                $slotCount++;
                $current->addMinutes($duration);
            }

            $sortedAppointments = $dateAppointments->sortBy('date');

            if ($sortedAppointments->count() > $slotCount) {
                $colliding = $colliding->concat($sortedAppointments->slice(0, $slotCount));
            } else {
                $colliding = $colliding->concat($sortedAppointments);
            }
        }

        return $colliding->values();
    }
}