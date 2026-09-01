<?php

namespace App\Services\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;

class SmartBookAppointment
{
    public static function execute($patient, $doctor, $date, $duration, $status)
    {
        //ASSUMBTION
            //PATIENT CAN ONLY HAVE ONE APPOINTMENT
            
        //USED IN WHATSAPP TOOL


        //FETCH CURRENT APPOINTMENT
        $appointment = Appointment::where('doctor_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->whereIn('status', AppointmentStatus::working())
            ->first();

        if ($appointment) {
            //RESCHEDULE
            $appointment->update([
                'date' => $date,
                'duration' => $duration,
                'status' => $status,
            ]);
        } else {
            //BOOK
            $appointment = Appointment::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'date' => $date,
                'duration' => $duration,
                'status' => $status,
            ]);
        }

        return $appointment->fresh();
    }
}