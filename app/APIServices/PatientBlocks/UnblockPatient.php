<?php

namespace App\APIServices\PatientBlocks;

use App\Models\Patient;
use Illuminate\Http\Request;
use App\Services\PatientBlocks\UnblockPatient as UnblockService;

class UnblockPatient
{
    public static function execute(Request $request, Patient $patient)
    {
        return UnblockService::execute(
            $request->user(),
            $patient,
        );
    }
}