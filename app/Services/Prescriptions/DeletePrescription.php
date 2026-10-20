<?php

namespace App\Services\Prescriptions;

use App\Models\Prescription;

class DeletePrescription
{
    public static function execute(Prescription $prescription)
    {
        return $prescription->delete();
    }
}
