<?php

namespace App\APIServices\Prescriptions;

use App\Models\Prescription;
use App\Services\Prescriptions\ShowPrescription as ShowService;

class ShowPrescription
{
    public static function execute(?Prescription $prescription)
    {
        return $prescription ? FormatPrescription::execute(ShowService::execute($prescription)) : null;
    }
}
