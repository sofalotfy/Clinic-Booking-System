<?php

namespace App\APIServices\Appointments;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ListAppointments
{
    public static function execute()
    {
        $appointments =  Appointment::leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
                                    ->leftJoin('users', 'patients.user_id', '=', 'users.id');

        $appointments = $appointments->select(self::getSelects())->get();

        $appointments->transform(function ($appointment){
            $appointment->avatar = $appointment->avatar
                ? asset('storage/' . $appointment->avatar)
                : null;

            return $patient;
        });

        return $appointments;
    }

    private static function getSelects()
    {
        return [
            "appointments.id",
            "appointments.patient_id",
            "appointments.duration",
            "appointments.status",
            DB::raw('DATE_ADD(appointments.date, INTERVAL COALESCE(appointments.delay, 0) MINUTE) as date'),
            "appointments.status",
            "appointments.grade",
            "users.name as patient_name",
            "users.phone as patient_phone",
            "users.image as avatar",
            DB::raw('(users.age + TIMESTAMPDIFF(YEAR, users.created_at, CURDATE())) as patient_age'),
            "users.gender as patient_gender",
            "users.address as patient_address",
        ];
    }
}