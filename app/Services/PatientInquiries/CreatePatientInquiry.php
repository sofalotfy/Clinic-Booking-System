<?php

namespace App\Services\PatientInquiries;

use App\Models\PatientInquiry;
use App\Models\Doctor;
use App\Services\Notifications\Notify;
use App\Enums\NotificationsType;

class CreatePatientInquiry
{
    public static function execute(
        int $doctorId,
        int $patientId,
        $question,
    ): PatientInquiry {
        $inquiry = PatientInquiry::Create(
            [
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
                'question' => $question,
            ]
        );
        
        Notify::execute(
            Doctor::find($doctorId),
            NotificationsType::INQUIRY,
            'New Patient Inquiry',
            "Patient {$inquiry->patient->user->name} has submitted a new inquiry: {$question}",
        );

        return $inquiry;
    }
}