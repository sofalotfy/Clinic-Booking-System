<?php

namespace App\AdminServices\Doctors;

use App\Models\Doctor;
use App\Services\Doctors\ReinstateDoctor as ReinstateDoctorService;

class ReinstateDoctor
{
    public static function execute($request, Doctor $doctor)
    {
        return ReinstateDoctorService::execute($doctor, $request->user());
    }
}
