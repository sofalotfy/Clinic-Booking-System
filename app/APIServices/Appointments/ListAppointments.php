<?php

namespace App\APIServices\Appointments;

use App\Services\Appointments\Retrievals\ListAppointments as ListService;
use Illuminate\Support\Facades\DB;

class ListAppointments
{
    public static function execute($request)
    {
       return ListService::execute($request->user())
        ->select(self::getSelects())
        ->get()
        ->transform(function ($appointment){
            $appointment->avatar = $appointment->avatar
                ? asset('storage/' . $appointment->avatar)
                : null;

            return $appointment;
        });
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