<?php

namespace App\APIServices\Notes;

use App\Models\Note;

class NotePatient
{
    public static function execute($patientId,  $note)
    {
        $doctorId = auth()->user()->doctor->id;

        return Note::create([
            'text' => $note,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
        ]);
    }
}