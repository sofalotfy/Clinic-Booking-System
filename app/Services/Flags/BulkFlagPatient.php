<?php

namespace App\Services\Flags;

use App\Models\Flag;
use App\Services\Patients\Checks\IsOldPatient;

class BulkFlagPatient
{
    public static function execute($user, $flag, $patientsIds)
    {
        foreach ($patientsIds as $patientId) {
            abort_unless(
                IsOldPatient::execute($user->clinicDoctorId(), $patientId),
                403,
                'You do not have permission to perform this action.'
            );
        }

        return $flag->patients()->syncWithoutDetaching(
            collect($patientsIds)->mapWithKeys(fn ($id) => [
                $id => ['doctor_id' => $user->clinicDoctorId()],
            ])->toArray()
        );
    }
}