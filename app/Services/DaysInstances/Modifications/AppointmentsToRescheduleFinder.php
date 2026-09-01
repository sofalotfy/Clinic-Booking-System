<?php

namespace App\Services\DaysInstances\Modifications;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Collection;

class AppointmentsToRescheduleFinder
{
    /**
     * Appointments on a given day, for a given doctor, that are
     * still active/queued/pending and therefore need reslotting.
     */
    public static function forDay($day): Collection
    {
        return Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->whereIn('status', AppointmentStatus::working())
            ->orderBy('date')
            ->get();
    }
}