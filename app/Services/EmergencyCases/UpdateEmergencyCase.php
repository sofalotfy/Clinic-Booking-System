<?php

namespace App\Services\EmergencyCases;

use App\Models\EmergencyCase;

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

        if (!$emergencyCase && empty($emergencyCase->symptoms)) {
            throw new \RuntimeException('Emergency case not found.');
        }

        $emergencyCase = EmergencyCase::updateOrCreate(
            [
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
            ],
            array_filter(
                $data,
                fn($value) => $value !== null
            )
        );

        return $emergencyCase->refresh();
    }
}