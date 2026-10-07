<?php

namespace App\APIServices\Clinics;

use Illuminate\Http\Request;
use App\Models\Clinic;

class ShowClinic
{
    public static function execute(Request $request, Clinic $clinic)
    {
        $clinic->description = $clinic->doctor?->description;
        $clinic->makeHidden('doctor');
        return $clinic;
    }
}
