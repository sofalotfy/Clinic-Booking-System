<?php

namespace App\Services\Appointments\Checks;

use App\Enums\AppointmentType;
use App\Models\Patient;
use Carbon\Carbon;

class ResolveAppointmentType
{
    /**
     * A booking is a consultation (follow-up) when the patient's latest
     * prescription from this doctor has a consultation date on or after the
     * booking's day; anything else is a new visit. A patient new to the
     * clinic ($patient === null) can only be a visit.
     */
    public static function execute(int $doctorId, ?Patient $patient, $date): AppointmentType
    {
        if (! $patient) {
            return AppointmentType::VISIT;
        }

        $consultationDate = $patient->activePrescription($doctorId)?->consultation_date;

        if (! $consultationDate) {
            return AppointmentType::VISIT;
        }

        return Carbon::parse($date)->startOfDay()->lte(Carbon::parse($consultationDate)->startOfDay())
            ? AppointmentType::CONSULTATION
            : AppointmentType::VISIT;
    }
}
