<?php

namespace App\APIServices\Patients;

use App\Enums\Gender;
use App\Models\Patient;
use App\Services\Patients\Checks\IsOldPatient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdatePatient
{
    public static function execute($request, Patient $patient)
    {
        abort_unless(
            IsOldPatient::execute($request->user()->clinicDoctorId(), $patient->id),
            403,
            'You do not have permission to perform this action.'
        );

        $validated = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($patient->user_id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('users', 'phone')->ignore($patient->user_id),
            ],
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'area' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ])->validate();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $validated = array_filter($validated, function ($value) {
            return !is_null($value);
        });

        $patient->user()->update($validated);

        return ShowPatient::execute($request, $patient);
    }
}
