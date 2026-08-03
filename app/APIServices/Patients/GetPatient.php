<?php

namespace App\APIServices\Patients;

use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientBlock;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GetPatient
{
    public static function execute(int $userId = null, int $patientId)
    {
        if(!$userId){
            $userId = auth()->user()->id;
        }
        $query = self::getAuthData($userId);

        if (!$query) {
            return null;
        }

        return $query
            ->where('patients.id', $patientId)
            ->select(self::getSelects())
            ->firstOrFail();
    }

    private static function getSelects(): array
    {
        return [
            'patients.id',

            'users.name',
            'users.phone',
            'users.email',
            'users.image as avatar',
            'users.age',
            'users.area',

            DB::raw('DATE(appointment_summary.last_appointment) as last_appointment_date'),
            DB::raw('TIME(appointment_summary.last_appointment) as last_appointment_time'),

            DB::raw('DATE(appointment_summary.upcoming_appointment) as upcoming_appointment_date'),
            DB::raw('TIME(appointment_summary.upcoming_appointment) as upcoming_appointment_time'),

            'patient_blocks.reason as block_reason',
            'patient_blocks.blocked_at',
            'patient_blocks.expires_at',

            DB::raw("
                CASE
                    WHEN patient_blocks.id IS NULL THEN 0
                    ELSE 1
                END AS is_blocked
            "),
        ];
    }

    private static function getAuthData(int $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->type !== UserType::DOCTOR) {
            return null;
        }

        $doctorId = $user->doctor->id;

        return Patient::query()
            ->leftJoin('appointments', function ($join) use ($doctorId) {
                $join->on('patients.id', '=', 'appointments.patient_id')
                    ->where('appointments.doctor_id', $doctorId);
            })
            ->leftJoin('users', 'patients.user_id', '=', 'users.id')
            ->leftJoinSub(
                self::getAppointmentSummaryData($doctorId),
                'appointment_summary',
                function ($join) {
                    $join->on('patients.id', '=', 'appointment_summary.patient_id');
                }
            )
            ->leftJoin('patient_blocks', function ($join) use ($doctorId) {
                $join->on('patients.id', '=', 'patient_blocks.patient_id')
                    ->where('patient_blocks.doctor_id', $doctorId)
                    ->whereNull('patient_blocks.unblocked_at')
                    ->where(function ($query) {
                        $query->whereNull('patient_blocks.expires_at')
                            ->orWhere('patient_blocks.expires_at', '>', now());
                    });
            });
    }

    private static function getAppointmentSummaryData(int $doctorId)
    {
        return Appointment::query()
            ->select(
                'patient_id',
                DB::raw('MAX(CASE WHEN date < NOW() THEN date END) as last_appointment'),
                DB::raw('MIN(CASE WHEN date >= NOW() THEN date END) as upcoming_appointment')
            )
            ->where('doctor_id', $doctorId)
            ->groupBy('patient_id');
    }
}