<?php

namespace App\APIServices\Appointments;

use App\Models\Appointment;
use Illuminate\Http\Request;

class BulkUpdateAppointments
{
    public static function execute(Request $request)
    {
        //DECODE SCHEMA
        $appointmentContainers = $request->updates;

        //INITIATE ARRAY
        $appointments = [];

        foreach ($appointmentContainers as $appointmentContainer) {
            if (is_array($appointmentContainer) && isset($appointmentContainer['id'])) {
                //FETCH APPOINTMENT
                $appointment = Appointment::findOrFail($appointmentContainer['id']);

                //GET CORRESPONDING CHANGES
                $changes = $appointmentContainer['changes'] ?? $appointmentContainer;

                //CALL CENTRALIZED SERVICE
                $appointments[] = UpdateAppointment::execute($request->user(), $appointment, $changes);
            }
        }

        return $appointments;
    }
}