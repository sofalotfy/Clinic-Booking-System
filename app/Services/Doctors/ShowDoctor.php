<?php

namespace App\Services\Doctors;

use App\Models\Doctor;

class ShowDoctor
{
    public static function execute(Doctor $doctor)
    {
        return $doctor->load([
            'user',
            'clinic',
            'whatsappAccount',
        ]);
    }
}
