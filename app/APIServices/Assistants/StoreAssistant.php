<?php

namespace App\APIServices\Assistants;

use App\Services\Assistants\StoreAssistant as StoreService;

class StoreAssistant
{
    public static function execute($request)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);

        $doctor_id = $request->user()->clinicDoctorId();

        return StoreService::execute($doctor_id, $validated);
    }
}