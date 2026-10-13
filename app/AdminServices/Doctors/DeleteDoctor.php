<?php

namespace App\AdminServices\Doctors;

use App\Models\Doctor;
use App\Services\Doctors\DeleteDoctor as DeleteDoctorService;

class DeleteDoctor
{
    public static function execute(Doctor $doctor)
    {
        return DeleteDoctorService::execute($doctor);
    }
}
