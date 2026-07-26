<?php

namespace App\Services\Appointments;

use App\Models\Day;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use App\APIServices\Appointments\UpdateAppointment;

class BulkUpdateAppointment
{
    public static function execute(array $updates): void
    {
        DB::transaction(function () use ($updates) {

            $appointments = Appointment::whereIn(
                'id',
                collect($updates)->pluck('id')
            )->get()->keyBy('id');

            // Build a lookup by appointment id
            $changes = collect($updates)->keyBy('id');

            // Group by doctor + date
            $groups = [];

            foreach ($appointments as $appointment) {

                $key = $appointment->doctor_id . '_' .
                    Carbon::parse($appointment->date)->toDateString();

                $groups[$key][] = $appointment;
            }

            foreach ($groups as $groupAppointments) {

                $appointment = $groupAppointments[0];

                $appointmentDate = Carbon::parse($appointment->date)->toDateString();

                $day = Day::where('doctor_id', $appointment->doctor_id)
                    ->where('date', $appointmentDate)
                    ->firstOrFail();

                // Generate valid slots
                $slots = [];

                $current = Carbon::parse(
                    "{$day->date} {$day->start_time}"
                );

                $end = Carbon::parse(
                    "{$day->date} {$day->end_time}"
                );

                while (
                    $current->copy()
                        ->addMinutes($day->appointment_duration)
                        ->lte($end)
                ) {
                    $slots[$current->format('H:i')] = true;

                    $current->addMinutes($day->appointment_duration);
                }

                // Occupied slots by appointments NOT being updated
                $occupied = [];

                Appointment::whereDate('date', $day->date)
                    ->where('doctor_id', $day->doctor_id)
                    ->whereNotIn(
                        'id',
                        collect($groupAppointments)->pluck('id')
                    )
                    ->get()
                    ->each(function ($appointment) use (&$occupied) {
                        $occupied[
                            Carbon::parse($appointment->date)
                                ->format('H:i')
                        ] = true;
                    });

                // Validate requested times
                foreach ($groupAppointments as $appointment) {

                    if (!$changes->has($appointment->id)) {
                        continue;
                    }

                    $change = $changes[$appointment->id]['changes'];

                    if (!isset($change['time'])) {
                        continue;
                    }

                    $time = $change['time'];

                    if (!isset($slots[$time])) {
                        throw new Exception(
                            "Invalid slot {$time} for appointment {$appointment->id}"
                        );
                    }

                    if (isset($occupied[$time])) {
                        throw new Exception(
                            "Slot {$time} is already occupied."
                        );
                    }

                    $occupied[$time] = true;
                }
            }

            // Everything is valid -> apply updates
            foreach ($updates as $appointment) {
                UpdateAppointment::execute($appointment);
            }
        });
    }
}