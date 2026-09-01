<?php

namespace App\Services\Notes;

use App\Models\Note;
use App\Models\User;
use App\Models\Patient;

class NotePatient
{
    public static function execute(User $user, Patient $patient, string $text)
    {
        return Note::create([
            'doctor_id' => $user->clinicDoctorId(),
            'patient_id' => $patient->id,
            'text' => $text,
        ]);
    }
}