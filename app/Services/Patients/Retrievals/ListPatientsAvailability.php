<?php

namespace App\Services\Patients\Retrievals;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;

class ListPatientsAvailability
{
    /**
     * Build the query for all of a doctor's patients (anyone who has ever
     * had an appointment with the doctor).
     */
    public static function execute(User $user)
    {
        $doctor = $user->clinicDoctor();

        return Patient::query()
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->whereHas(
                'appointments',
                fn ($query) => $query->where('doctor_id', $doctor->id)
            );
    }
}
