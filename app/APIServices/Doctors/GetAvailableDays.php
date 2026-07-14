<?php

namespace App\APIServices\Doctors;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetAvailableDays
{
    public static function execute(int $doctorId): array
    {
        $start = now()->startOfDay();
        $end = now()->addMonth()->endOfDay();

        $days = Day::where('doctor_id', $doctorId)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        $appointmentsCounts = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', [
                AppointmentStatus::ACTIVE,
                AppointmentStatus::QUEUED,
            ])
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(date)'))
            ->pluck('count', 'day');

        $availableDays = [];

        foreach ($days as $day) {

            $workingMinutes = Carbon::parse($day->start_time)
                ->diffInMinutes(Carbon::parse($day->end_time));

            $maxAppointments = floor($workingMinutes / $day->appointment_duration)
                + $day->queue_length;

            $appointmentsCount = $appointmentsCounts[$day->date] ?? 0;

            if ($appointmentsCount < $maxAppointments) {
                $availableDays[] = $day;

                if (count($availableDays) === 7) {
                    break;
                }
            }
        }

        return $availableDays;
    }
}