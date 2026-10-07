<?php

namespace App\AdminServices\Dashboard;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;

class GetOverview
{
    public static function execute()
    {
        return [
            'doctors' => Doctor::count(),
            'patients' => Patient::count(),
            'appointments' => Appointment::count(),
        ];
    }
}
