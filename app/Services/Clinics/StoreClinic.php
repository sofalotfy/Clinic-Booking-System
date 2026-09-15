<?php

namespace App\Services\Clinics;

use App\Models\Clinic;

class StoreClinic
{
    public static function execute($user, $data)
    {
        return Clinic::create([
            'name' => $data['name'],
            'doctor_id' => $user->clinicDoctorId(),
            'location_link' => $data['location_link'] ?? null,
            'facebook' => $data['facebook'] ?? null,
            'instgram' => $data['instgram'] ?? null,
            'linkedin' => $data['linkedin'] ?? null,
            'vezeeta' => $data['vezeeta'] ?? null,
        ]);
    }
}
