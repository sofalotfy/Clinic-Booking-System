<?php

namespace App\Services\Notifications\Doctor\Profile;

use App\Models\Notification;
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
                'sender_id'  => $patient->user_id,
                'receiver_id' => $doctor->user_id,
                'doctor_id' => $doctor->id,
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