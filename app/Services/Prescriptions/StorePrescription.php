<?php

namespace App\Services\Prescriptions;

use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;

class StorePrescription
{
    public static function execute(Appointment $appointment, array $data): Prescription
    {
        return DB::transaction(function () use ($appointment, $data) {
            $prescription = Prescription::create([
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'patient_id' => $appointment->patient_id,
                'diagnosis' => $data['diagnosis'] ?? null,
                'notes' => $data['notes'] ?? null,
                'consultation_date' => $data['consultation_date'] ?? null,
            ]);

            SyncPrescriptionItems::execute($prescription, $data);

            return ShowPrescription::execute($prescription);
        });
    }
}
