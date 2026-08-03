<?php

namespace App\APIServices\Appointments;

use App\models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Enums\AppointmentStatus;
use App\Services\Appointments\NotifyReshedule;

class UpdateAppointment
{
    public static function execute($appointment, $changes)
    {
        //save old state
        $oldStatus = $appointment->status;
        $oldDate = $appointment->date;
        
        //validate
        $validated = self::validate($changes);
        
        //prepare data
        $data = collect($validated)
            ->only(['status', 'grade'])
            ->filter(fn ($value, $key) => $key === 'grade' || filled($value))
            ->toArray();

        if (filled($validated['time'] ?? null)) {
            $data['date'] = self::formatDate($appointment->date, $validated['time']);
        }

        //update appointment
        if ($data) {
            $appointment->update($data);
            $appointment->refresh();
        }

        //notify if status or time changed
        self::notifyIfNeeded($appointment, $oldStatus, $oldDate);

        return $appointment;
    }    

    private static function validate($changes)
    {
        //  validate
        $validator = Validator::make($changes, [
            'status' => ['nullable', 'string', \Illuminate\Validation\Rule::enum(\App\Enums\AppointmentStatus::class)],
            'grade'  => ['nullable', 'string', \Illuminate\Validation\Rule::enum(\App\Enums\AppointmentGrade::class)],
            'time'   => ['nullable', 'string', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
    private static function notifyIfNeeded($appointment, $oldStatus, $oldDate)
    { 
        if (
            $oldStatus !== $appointment->status
        ) {
            if ($appointment->status == AppointmentStatus::CANCELLED) {
                NotifyReshedule::execute($appointment, null, AppointmentUpdateNotificationTypes::CANCEL);
            }

            if ($appointment->status == AppointmentStatus::QUEUED) {
                NotifyReshedule::execute($appointment, null, AppointmentUpdateNotificationTypes::OVERFLOW);
            }

            return;
        }

        if ($oldDate !== $appointment->date) {

            NotifyReshedule::execute($appointment, $appointment->date, AppointmentUpdateNotificationTypes::RESHEEDULE);
        }
    }
    private static function formatDate(string $dateTime, string $time): string
    {
        $date = Carbon::parse($dateTime);
        [$hour, $minute] = explode(':', $time);

        return $date
            ->setTime($hour, $minute)
            ->toDateTimeString();
    }
}
