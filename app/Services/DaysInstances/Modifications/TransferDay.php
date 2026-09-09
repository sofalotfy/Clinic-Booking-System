<?php

namespace App\Services\DaysInstances\Modifications;

use App\Models\Day;
use App\Models\User;
use Carbon\Carbon;   
use Illuminate\Support\Facades\DB;
use App\Models\Appointment;
use App\Services\Appointments\Modifications\ResheduleAppointment;
use App\Services\DaysInstances\Creations\ReplicateDay;
use App\Services\DaysInstances\Modifications\DeleteDay;

class TransferDay
{
    public static function execute(User $user, Day $day, $date)
    {
        DB::transaction(function () use ($user, $day, $date) {
            $appointments = self::fetchDayAppointments($day);
            $newDay = ReplicateDay::execute($user, $day, date: $date);
            self::syncAppointments($user, $appointments, $newDay);
            DeleteDay::execute($user, $day);
        });
    }

    private static function fetchDayAppointments(Day $day)
    {
        return Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->get();
    }

    private static function syncAppointments($user, $appointments, Day $newDay)
    {
        foreach($appointments as $appointment)
        {
            $newDateTime = Carbon::parse($newDay->date)
                ->setTimeFrom(Carbon::parse($appointment->date));

            ResheduleAppointment::execute($user, $appointment, $newDateTime);
        }
    }
}