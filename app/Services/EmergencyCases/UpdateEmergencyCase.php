<?php

namespace App\Services\EmergencyCases;

use App\Models\EmergencyCase;
use App\Services\EmergencyCases\CreateEmergencyCase;

class UpdateEmergencyCase
{
    public static function execute(
        int $doctorId,
        int $patientId,
        array $data,
    ) {
        $emergencyCase = EmergencyCase::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->first();

        if (!$emergencyCase) {
            if (empty($data['symptoms'])) {
                throw new \RuntimeException(
                    'Cannot create an emergency case without symptoms.'
                );
            }

            $emergencyCase = CreateEmergencyCase::execute(
                patientId: $patientId,
                doctorId: $doctorId,
                symptoms: $data['symptoms'],
            );
        }

        $emergencyCase->update(
            array_filter(
                $data,
                fn ($value) => $value !== null
            )
        );

        return $emergencyCase->refresh();
    }
}