<?php

namespace App\Services\Patients\Retrievals;

use App\Models\User;
use App\Models\Patient;
use App\Services\Appointments\Retrievals\ListAppointments;

class ListDoctorPatients
{
    public static function execute(User $user, $filters = null)
    {
        $patients = Patient::leftJoin('appointments', 'appointments.patient_id', '=', 'patients.id')
                        ->leftJoin('users', 'patients.user_id', '=', 'users.id')
                        ->where('appointments.doctor_id', $user->clinicDoctorId())
                        ->when($filters, fn ($builder) => self::filter($builder, $filters))
                        ->groupBy('patients.id');

        return $patients;
    }

    private static function filter($builder, $filters)
    {
        return $builder
            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '>=', $filters['date_from']);
            })

            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '<=', $filters['date_to']);
            })

            ->when(isset($filters['age_from']), function ($query) use ($filters) {
                $query->where('users.age', '>=', $filters['age_from']);
            })

            ->when(isset($filters['age_to']), function ($query) use ($filters) {
                $query->where('users.age', '<=', $filters['age_to']);
            })

            ->when(
                isset($filters['has_upcoming_appointment'])
                    && $filters['has_upcoming_appointment'],
                function ($query) {
                    $query->havingRaw(
                        'MIN(CASE WHEN appointments.date >= NOW() THEN appointments.date END) IS NOT NULL'
                    );
                }
            )

            ->when(isset($filters['search']), function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->where('users.name', 'like', "%{$filters['search']}%")
                        ->orWhere('users.phone', 'like', "%{$filters['search']}%")
                        ->orWhere('users.area', 'like', "%{$filters['search']}%");
                });
            })

            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '<=', $filters['date_to']);
            });
    }
}