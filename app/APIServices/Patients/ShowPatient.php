<?php

namespace App\APIServices\Patients;

use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientBlock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\Patients\Retrievals\ShowPatient as ShowService;

class ShowPatient
{
    public static function execute($request, Patient $patient)
    {
        $patient = ShowService::execute($request->user(), $patient)
            ->select(self::getSelects())
            ->get();

        return response()->json([
            'patient' => $patient,
        ]);
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

            DB::raw(
                'DATE(MAX(CASE WHEN appointments.date < NOW() THEN appointments.date END)) as last_appointment_date'
            ),

            DB::raw(
                'TIME(MAX(CASE WHEN appointments.date < NOW() THEN appointments.date END)) as last_appointment_time'
            ),

            DB::raw(
                'DATE(MIN(CASE WHEN appointments.date >= NOW() THEN appointments.date END)) as upcoming_appointment_date'
            ),

            DB::raw(
                'TIME(MIN(CASE WHEN appointments.date >= NOW() THEN appointments.date END)) as upcoming_appointment_time'
            ),

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
}