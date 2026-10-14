<?php

namespace App\Services\Doctors;

use App\Models\Doctor;
use App\Models\User;

class ReinstateDoctor
{
    public static function execute(Doctor $doctor, ?User $actor = null): int
    {
        $lifted = $doctor->suspensions()
            ->active()
            ->update([
                'lifted_by' => $actor?->id,
                'lifted_at' => now(),
            ]);

        $doctor->unsetRelation('activeSuspension');

        return $lifted;
    }
}
