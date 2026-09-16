<?php

namespace App\APIServices\Doctors;

use Illuminate\Validation\Rule;
use App\Services\Doctors\UpdateDoctor as UpdateService;
use App\Models\Doctor;
use App\Enums\Gender;

class UpdateDoctor
{
    public static function execute($request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($doctor->user_id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('users', 'phone')->ignore($doctor->user_id),
            ],
            'password' => 'nullable|string|min:8',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'area' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        return UpdateService::execute($doctor, $validated, $request);
    }
}
