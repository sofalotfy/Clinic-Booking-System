<?php

namespace App\APIServices\Appointments;

use App\Enums\AppointmentGrade;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\Appointments\Modifications\UpdateAppointment as UpdateService;

class UpdateAppointment
{
    public static function execute($user, $appointment, $changes)
    {
        $validated = self::validate($changes);

        return UpdateService::execute($user,  $appointment, $validated['status'] ?? null, $validated['time'] ?? null, $validated['grade'] ?? null);
    }

    private static function validate($changes)
    {
        $validator = Validator::make($changes, [
            'status' => [
                'nullable',
                'string',
                Rule::enum(AppointmentStatus::class),
            ],

            'grade' => [
                'nullable',
                'string',
                Rule::enum(AppointmentGrade::class),
            ],

            'time' => [
                'nullable',
                'string',
                'regex:/^\d{1,2}:\d{2}(:\d{2})?$/',
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}