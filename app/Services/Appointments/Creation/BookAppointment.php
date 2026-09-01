<?php

namespace App\Services\Appointments\Creation;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Enums\UserType;
use App\Services\TemplatePlans\Checks\CheckSlotAvailability;
use App\Services\TemplatePlans\Checks\CheckSlotExistance;
use App\Services\TemplatePlans\Checks\CheckAvailability;
use App\Services\Patients\Checks\CheckBookingLimitExceeded;
use Illuminate\Validation\ValidationException;

class BookAppointment
{
    public static function execute($user, $patient, $day, $time, $status = AppointmentStatus::ACTIVE)
    {   
        if (!$day) {
            throw ValidationException::withMessages([
                'error' => 'The day is not available.',
            ]);
        }

        if (CheckBookingLimitExceeded::execute($day->doctor_id, $patient->id)) {
            throw ValidationException::withMessages([
                'error' => 'You have reached the maximum number of appointments.',
            ]);
        }
        if($status == AppointmentStatus::ACTIVE){
            if (!CheckSlotExistance::execute($day, $time)) {
                throw ValidationException::withMessages([
                    'error' => 'This slot is not available.',
                ]);
            }
            if (!CheckSlotAvailability::execute($day, $time)) {
                throw ValidationException::withMessages([
                    'error' => 'This slot is already booked.',
                ]);
            }
        }
        else{
            if (!CheckAvailability::execute($day)) {
                throw ValidationException::withMessages([
                    'error' => 'The Day is fully booked.',
                ]);
            }
        }

        //BOOK APPOINTMENT
        $appointment = Appointment::create([
            'doctor_id' => $day->doctor_id,
            'patient_id' => $patient->id,
            'date' => $day->date . ' ' . $time,
            'duration' => $day->appointment_duration,
            'status' => $status,
        ]);

        return $appointment;
    }
}