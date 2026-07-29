<?php

namespace App\APIServices\Appointments;

use App\Models\Appointment;
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
    public static function execute($appointment)
    {
        $model = Appointment::findOrFail($appointment['id']);
        $oldStatus = $model->status;
        $oldDate = $model->date;
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
            $model->refresh();
        }

        self::notifyIfNeeded($model, $oldStatus, $oldDate);
        
    }    

    private static function updateTime(string $dateTime, string $time): string
    {
        $date = Carbon::parse($dateTime);
        [$hour, $minute] = explode(':', $time);

        return $date
            ->setTime($hour, $minute)
            ->toDateTimeString();
    }

    private static function notifyIfNeeded($model, $oldStatus, $oldDate)
    {
        \Log::info('notification check', [
            'oldStatus' => $oldStatus,
            'newStatus' => $model->status,
            'oldDate' => $oldDate,
            'newDate' => $model->date,
        ]);
        if (
            $oldStatus !== $model->status
        ) {
            \Log::info('notification check', [
                'status changed' => true
            ]);
            if ($model->status == AppointmentStatus::CANCELLED) {
                \Log::info('notification check', [
                    'cancel' => true
                ]);
                NotifyReshedule::execute($model, null, AppointmentUpdateNotificationTypes::CANCEL);
            }

            if ($model->status == AppointmentStatus::QUEUED) {
                \Log::info('notification check', [
                    'queued' => true
                ]);
                NotifyReshedule::execute($model, null, AppointmentUpdateNotificationTypes::OVERFLOW);
            }

            return;
        }

        if ($oldDate !== $model->date) {
            \Log::info('notification check', [
                'date changed' => true
            ]);
            NotifyReshedule::execute($model, $model->date, AppointmentUpdateNotificationTypes::RESHEEDULE);
        }
    }

}
