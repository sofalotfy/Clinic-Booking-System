<?php

namespace App\Services\Appointments\Retrievals;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ListAppointments
{
    public static function execute($user, $filters = null)
    {
        //FETCH APPOINTMENTS FOR DOCTOR
        $builder =  Appointment::leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
                        ->leftJoin('users', 'patients.user_id', '=', 'users.id')
                        ->where('appointments.doctor_id', $user->clinicDoctorId())
                        ->when($filters, fn ($builder) => self::filter($builder, $filters));

        return $builder;
    }

    private static function filter($builder, $filters)
    {
        return $builder
            ->when(isset($filters['patient_id']), function ($query) use ($filters) {
                $query->where('appointments.patient_id', $filters['patient_id']);
            })
            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '<=', $filters['date_to']);
            });
    }
}