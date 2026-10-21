<?php

namespace App\APIServices\Prescriptions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\Prescriptions\StorePrescription as StoreService;
use Illuminate\Validation\ValidationException;

class StorePrescription
{
    public static function execute($request, Appointment $appointment)
    {
        if ($appointment->status === AppointmentStatus::CANCELLED) {
            throw ValidationException::withMessages(['appointment' => 'A cancelled appointment cannot have a prescription.']);
        }

        if ($appointment->prescription()->exists()) {
            throw ValidationException::withMessages(['appointment' => 'This appointment already has a prescription, update it instead.']);
        }

        $validated = $request->validate(PrescriptionRules::rules($appointment), PrescriptionRules::messages());

        return FormatPrescription::execute(StoreService::execute($request->user(), $appointment, $validated));
    }
}
