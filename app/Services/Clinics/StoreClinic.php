<?php

namespace App\Services\Clinics;

use App\Models\Clinic;

class StoreClinic
{
    public static function execute($user, $data, $doctorId = null)
    {
        return Clinic::create([
            'name' => $data['name'],
            'doctor_id' => $doctorId ?? ($user ? $user->clinicDoctorId() : null),
            'location_link' => $data['location_link'] ?? null,
            'address' => $data['address'] ?? null,
            'facebook' => $data['facebook'] ?? null,
            'instgram' => $data['instgram'] ?? null,
            'linkedin' => $data['linkedin'] ?? null,
            'vezeeta' => $data['vezeeta'] ?? null,
            'clinic_phone' => $data['clinic_phone'] ?? null,
            'notifications_phone' => $data['notifications_phone'] ?? null,
        ]);
    }
}
