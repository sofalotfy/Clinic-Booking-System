<?php

namespace App\Services\Doctors;

use App\Models\Doctor;
use Spatie\Permission\Models\Role;

class DeleteDoctor
{
    public static function execute(Doctor $doctor)
    {
        Role::where('name', 'doctor')
            ->where('doctor_id', $doctor->id)
            ->delete();

        return $doctor->delete();
    }
}
