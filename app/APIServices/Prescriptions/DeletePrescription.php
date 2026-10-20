<?php

namespace App\APIServices\Prescriptions;

use App\Models\Prescription;
use App\Services\Prescriptions\DeletePrescription as DeleteService;

class DeletePrescription
{
    public static function execute(Prescription $prescription)
    {
        return DeleteService::execute($prescription);
    }
}
