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
            return response()->json([
            'success' => true,
            'assistant_id' => $assistant->id,
            'user_id' => $assistant->user_id,
            'assistant' => $assistant,
            'user' => $assistant->user,
        ]);
            $contacts[] = [
                'name' => $assistant->user->name,
                'phone' => $assistant->user->phone,
            ];
        }

        return $contacts;
    }
}