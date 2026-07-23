<?php

namespace App\Services\DaysInstances;

use App\Models\Day;
use App\Models\Appointment;
use Carbon\Carbon;


use App\Enums\AppointmentStatus;
use App\Services\Appointments\QueueAppointment;

class GenerateDay
{
    public static function execute($template, $date, $force = false)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $templateDay = $template->templateDays
            ->firstWhere('day_of_week', $dayOfWeek);

        if (!$force && Appointment::whereDate('date', $date)->where('doctor_id', $template->doctor_id)->exists()) {
            return null;
        }

        if ($day = Day::where('date', $date)->where('doctor_id', $template->doctor_id)->first()) {
            $day->delete();
        }

        if (!$templateDay) {
            if ($force) {
                $appointments = Appointment::whereDate('date', $date)
                    ->where('doctor_id', $template->doctor_id)
                    ->whereIn('status', [AppointmentStatus::ACTIVE, AppointmentStatus::QUEUED, AppointmentStatus::PENDING])
                    ->get();
                foreach ($appointments as $appointment) {
                    $appointment->update([
                        'status' => AppointmentStatus::CANCELLED,
                        'isConfirmed'  => false,
                    ]);
                    NotifyReshedule::execute($appointment, null, 'truncate');
                }
            }
            return null;
        }

        $day = Day::create([
            'doctor_id' => $template->doctor_id,
            'date' => $date,
            'start_time' => $templateDay->start_time,
            'end_time' => $templateDay->end_time,
            'appointment_duration' => $templateDay->appointment_duration,
            'queue_length' => $templateDay->queue_length,
        ]);

        ReSheduleDay::execute($day);

        return $day;
    }
}