<?php

namespace App\Services\DaysInstances\Checks;

use App\Models\Day;

class CheckDayOccupied
{
    /**
     * A date is occupied when an active day already exists for the doctor.
     */
    public static function execute(int $doctorId, $date): bool
    {
        return Day::where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->active()
            ->exists();
    }
}
