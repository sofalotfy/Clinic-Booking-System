<?php

namespace App\APIServices\Appointments;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Enums\AppointmentStatus;

class BulkUpdateAppointments
{
    public static function execute(Request $request)
    {
        $appointmentContainers = $request->updates;

        foreach ($appointmentContainers as $appointmentContainer) {
            if (is_array($appointmentContainer) && isset($appointmentContainer['id'])) {

                $appointment = Appointment::findOrFail($appointmentContainer['id']);

                $changes = $appointmentContainer['changes'] ?? $appointmentContainer;

                UpdateAppointment::execute($appointment, $changes);
            }
        }

        return $appointmentContainers;
    }
}