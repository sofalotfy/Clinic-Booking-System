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
            ->when(isset($filters['date']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', $filters['date']);
            })
            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '<=', $filters['date_to']);
            })
            ->when(! empty($filters['status']), function ($query) use ($filters) {
                $query->whereIn('appointments.status', (array) $filters['status']);
            })
            ->when(! empty($filters['grade']), function ($query) use ($filters) {
                $query->whereIn('appointments.grade', (array) $filters['grade']);
            })
            ->when(isset($filters['is_confirmed']), function ($query) use ($filters) {
                $query->where('appointments.isConfirmed', (bool) $filters['is_confirmed']);
            })
            ->when(isset($filters['gender']), function ($query) use ($filters) {
                $query->where('users.gender', $filters['gender']);
            })
            ->when(isset($filters['search']), function ($query) use ($filters) {
                $query->where(fn ($query) => $query
                    ->where('users.name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('users.phone', 'like', '%'.$filters['search'].'%'));
            })
            ->when(isset($filters['has_prescription']), function ($query) use ($filters) {
                $filters['has_prescription']
                    ? $query->whereHas('prescription')
                    : $query->whereDoesntHave('prescription');
            })
            ->when(isset($filters['sort']), function ($query) use ($filters) {
                $query->orderBy('appointments.date', $filters['sort']);
            });
    }
}