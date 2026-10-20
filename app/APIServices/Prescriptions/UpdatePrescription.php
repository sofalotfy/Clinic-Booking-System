<?php

namespace App\APIServices\Prescriptions;

use App\Models\Prescription;
use App\Services\Prescriptions\UpdatePrescription as UpdateService;

class UpdatePrescription
{
    public static function execute($request, Prescription $prescription)
    {
        $validated = $request->validate(PrescriptionRules::rules($prescription->appointment), PrescriptionRules::messages());

        return FormatPrescription::execute(UpdateService::execute($prescription, $validated));
    }
}
