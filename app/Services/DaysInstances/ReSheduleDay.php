<?php

namespace App\Services\DaysInstances;

use App\Models\Day;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Enums\AppointmentStatus;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterval;
use App\Services\Appointments\ResheduleAppointment;
use App\Services\Appointments\QueueAppointment;


class ReSheduleDay
{
    public static function execute($day)
    {
        $appointments = Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->whereIn('status', [AppointmentStatus::ACTIVE, AppointmentStatus::QUEUED, AppointmentStatus::PENDING])
            ->orderBy('date')
            ->get();

        $slots = [];
        $start = Carbon::parse("{$day->date} {$day->start_time}");
        $end = Carbon::parse("{$day->date} {$day->end_time}");

        $current = $start->copy();
        while ($current->copy()->addMinutes($day->appointment_duration)->lte($end)) {
            $slots[] = $current->copy();
            $current->addMinutes($day->appointment_duration);
        }

        foreach ($appointments as $index => $appointment) {
            if ($index < count($slots)) {
                $slotTime = $slots[$index];
                ResheduleAppointment::execute($appointment, $slotTime->toDateTimeString(), $day->appointment_duration);
            } else {
                QueueAppointment::execute($appointment, $day->appointment_duration, 'overflow');
            }
        }
    }
}