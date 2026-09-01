<?php

namespace App\Services\DaysInstances\Retrievals;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetDayAppointments
{
    public static function execute($date, $user = null)
    {
        $user = $user ?? auth()->user();

        $days = Appointment::select(self::getSelects())
                    ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
                    ->leftJoin('users', 'patients.user_id', '=', 'users.id')
                    ->where('appointments.doctor_id', $user->clinicDoctorId())
                    ->whereDate('appointments.date', $date)
                    ->get();
                    
        return $days;
    }

    private static function getSelects()
    {
        return [
            "appointments.id",
            "appointments.patient_id",
            "appointments.duration",
            "appointments.status",
            DB::raw('DATE(DATE_ADD(appointments.date, INTERVAL COALESCE(appointments.delay, 0) MINUTE)) as date'),
            DB::raw('TIME(DATE_ADD(appointments.date, INTERVAL COALESCE(appointments.delay, 0) MINUTE)) as time'),
            "appointments.status",
            "appointments.grade",
            "users.name as patient_name",
            "users.phone as patient_phone",
            "users.image as avatar",
            DB::raw('(users.age + TIMESTAMPDIFF(YEAR, users.created_at, CURDATE())) as patient_age'),
            "users.gender as patient_gender",
            "users.area as patient_area",
        ];
    }
}