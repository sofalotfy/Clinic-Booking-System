<?php

namespace App\AdminServices\Doctors;

use App\Models\Doctor;
use App\Services\Doctors\SuspendDoctor as SuspendDoctorService;

class SuspendDoctor
{
    public static function execute($request, Doctor $doctor)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        return SuspendDoctorService::execute($doctor, $request->user(), $validated['reason'] ?? null);
    }
}
