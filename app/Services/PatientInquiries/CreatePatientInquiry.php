<?php

namespace App\Services\PatientInquiries;

use App\Models\PatientInquiry;

class CreatePatientInquiry
{
    public static function execute(
        int $doctorId,
        int $patientId,
        $question,
    ): PatientInquiry {
        return PatientInquiry::Create(
            [
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
                'question' => $question,
            ]
        );
    }
}