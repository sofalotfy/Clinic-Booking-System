<?php

namespace App\Services\Notifications\Doctor\Profile;

use App\Models\Notification;
use App\Enums\NotificationsType;
use App\Models\Doctor;
use App\Models\Appointment;

class PatientRename
{
    public static function execute($patient, $oldName, $newName)
    {
        $doctors = self::getRelatedDoctors($patient);

        foreach($doctors as $doctor)
        {
            Notification::create([
                'user_id'  => $doctor->user_id,
                'type'     => NotificationsType::PATIENT_PROFILE,
                'title' => 'Patient name changed',
                'text'  => "Patient $oldName has been renamed to $newName",
            ]);
        }
        
    }

    private static function getRelatedDoctors($patient)
    {
        $appointments = Appointment::where('patient_id', $patient->id)->get();
        $doctorIds = $appointments->pluck('doctor_id')->unique();
        return Doctor::whereIn('id', $doctorIds)->get();
    }
}