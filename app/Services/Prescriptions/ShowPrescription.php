<?php

namespace App\Services\Prescriptions;

use App\Models\Prescription;

class ShowPrescription
{
    public static function execute(Prescription $prescription): Prescription
    {
        return $prescription->load(['appointment', 'patient.user', 'medicines', 'labs', 'scans']);
    }
}
