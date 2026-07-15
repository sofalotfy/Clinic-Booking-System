<?php

namespace App\APIServices\Appointments;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateAppointment
{
    public static function execute($appointment)
    {
        \Log::info([
            'reached' => true,
        ]);

        $model = Appointment::findOrFail($appointment['id']);

        // Support both nested changes and flat fields
        $changes = $appointment['changes'] ?? $appointment;

        $validator = Validator::make($changes, [
            'status' => ['nullable', 'string', \Illuminate\Validation\Rule::enum(\App\Enums\AppointmentStatus::class)],
            'grade'  => ['nullable', 'string', \Illuminate\Validation\Rule::enum(\App\Enums\AppointmentGrade::class)],
            'time'   => ['nullable', 'string', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        \Log::info([
            'passed validation' => true,
        ]);
        $model->update([
            "status" =>  $validated['status']??$model['status'],
        ]);
        $data = [];

        if (array_key_exists('status', $validated) && $validated['status'] !== null && $validated['status'] !== '') {
            $data['status'] = $validated['status'];
        }

        if (array_key_exists('grade', $validated)) {
            $data['grade'] = $validated['grade'];
        }
        
        if (array_key_exists('time', $validated) && $validated['time'] !== null && $validated['time'] !== '') {
            $data['date'] = self::updateTime($model->date, $validated['time']);
        }

        if (!empty($data)) {
            $model->update($data);
        }
    }    

    private static function updateTime(string $dateTime, string $time): string
    {
        $date = Carbon::parse($dateTime);
        [$hour, $minute] = explode(':', $time);

        return $date
            ->setTime($hour, $minute)
            ->toDateTimeString();
    }
    
}
