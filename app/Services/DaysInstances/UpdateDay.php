<?php

namespace App\Services\DaysInstances;

use App\Models\Day;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Services\Appointments\NotifyReshedule;

use App\Enums\AppointmentStatus;


class UpdateDay
{
    public static function execute($day, $updates)
    {
        if(!auth()->user()->doctor || auth()->user()->doctor->id != $day->doctor_id){
            throw new \Exception('You are not authorized to perform this action');
        }

        $day->update($updates);
        ReSheduleDay::execute($day);

        return $day;
    }
}