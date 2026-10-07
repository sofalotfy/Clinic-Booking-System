<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\Carbon;

class CancelOverdueAppointments
{
    /**
     * Silently cancel every working appointment whose scheduled time has
     * already passed. Returns the number of appointments cancelled.
     */
    public static function execute(?Carbon $now = null): int
    {
        $now ??= Carbon::now();

        return Appointment::whereIn('status', AppointmentStatus::working())
            ->where('date', '<', $now)
            ->update([
                'status' => AppointmentStatus::CANCELLED,
            ]);
    }
}
