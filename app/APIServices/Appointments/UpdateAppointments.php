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
        $model = Appointment::findOrFail($appointment['id']);

        $data = [];

        if (!empty($appointment['status'])) {
            $data['status'] = $appointment['status'];
        }

        if (!empty($appointment['grade'])) {
            $data['grade'] = $appointment['grade'];
        }
        
        if (!empty($appointment['time'])) {
            $data['date'] = self::updateTime($model->date, $appointment['time']);
        }

        $model->update($data);
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