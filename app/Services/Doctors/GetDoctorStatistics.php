<?php

namespace App\Services\Doctors;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;

class GetDoctorStatistics
{
    public const WINDOW_DAYS = 30;

    public static function execute(Doctor $doctor): array
    {
        $appointments = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', '>=', now()->subDays(self::WINDOW_DAYS)->startOfDay())
            ->whereDate('date', '<=', now());

        $counts = (clone $appointments)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byStatus = [];
        foreach (AppointmentStatus::cases() as $status) {
            $byStatus[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        $upcomingAppointments = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', '>', now())
            ->whereDate('date', '<=', now()->addDays(self::WINDOW_DAYS)->endOfDay());

        return [
            'window_days' => self::WINDOW_DAYS,
            'appointments_total' => (int) $appointments->count(),
            'appointments_by_status' => $byStatus,
            'appointments_upcoming_total' => (int) $upcomingAppointments->count(),
            'patients' => (int) Appointment::query()
                ->where('doctor_id', $doctor->id)
                ->distinct()
                ->count('patient_id'),
            'blocked_patients' => $doctor->blockedPatients()->active()->count(),
            'assistants' => $doctor->assistants()->count(),
        ];
    }
}
