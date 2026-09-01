<?php

namespace App\Services\DaysInstances\Modifications;

use App\Models\Day;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Services\Appointments\NotifyReshedule;

use App\Enums\AppointmentStatus;


class UpdateDay
{
    public static function execute($user, $day, $updates=[])
    {
        $day->update($updates);
        
        SyncDayAppointments::execute($user, $day);

        return $day;
    }
}