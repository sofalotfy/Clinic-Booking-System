<?php

namespace App\Services\Assistants;

use App\Models\Assistant;
use App\Models\Doctor;

class GetAssistantsContacts
{
    public static function execute(Doctor $doctor)
    {
        $assistants = Assistant::where('doctor_id', $doctor->id)->get();
        $contacts = [];
        foreach ($assistants as $assistant) {
            $contacts[] = [
                'name' => $assistant->user()->name,
                'phone' => $assistant->user()->phone,
            ];
        }

        return $contacts;
    }
}