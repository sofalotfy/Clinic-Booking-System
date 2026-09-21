<?php

namespace App\Services\Appointments\Creation;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationEnum;
use App\Models\Appointment;
use App\Services\Notifications\NotificationManager;

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

        \Log::info('SmartBook lookup', [
            'found_id' => $appointment?->id,
            'created_at' => $appointment?->created_at,
            'old_date' => $appointment?->date,
            'new_date' => (string) $date,
        ]);
        $old_date = null;

        \Log::info("appointment is : {$appointment} : {$patient->user->name}");
        if ($appointment) {
            $old_date = $appointment->date;

            $appointment->update([
                'date' => $date,
                'duration' => $duration,
                'status' => $status,
            ]);

            $notificationType = NotificationEnum::PATIENT_APPOINTMENT_RESCHEDULED;
        } else {
            $appointment = Appointment::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'date' => $date,
                'duration' => $duration,
                'status' => $status,
            ]);

            $notificationType = NotificationEnum::PATIENT_APPOINTMENT_BOOKED;
        }

        $appointment = $appointment->fresh();

        if ($old_date) {
            $appointment->old_date = $old_date;
        }

        NotificationManager::execute($patient->user, $doctor->id, $notificationType, $appointment);

        return $appointment;
    }
}