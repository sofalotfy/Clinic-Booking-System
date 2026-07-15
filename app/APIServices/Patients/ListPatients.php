<?php

namespace App\APIServices\Patients;

use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Enums\UserType;
use Illuminate\Support\Facades\DB;


class ListPatients
{
    public static function execute(int $userId, int $perPage = 50,string $search = null, array $filters = null)
    {
        $user = User::findOrFail($userId);
        
        $appointments = self::getAuthData($userId);

        if(is_null($appointments)){
            return null;
        }

        if ($filters) {

            if (!is_null($filters['age_from'] ?? null)) {
                $appointments->where('users.age', '>=', $filters['age_from']);
            }

            if (!is_null($filters['age_to'] ?? null)) {
                $appointments->where('users.age', '<=', $filters['age_to']);
            }

            if (!is_null($filters['date_from'] ?? null)) {
                $appointments->whereDate(
                    'appointment_summary.last_appointment',
                    '>=',
                    $filters['date_from']
                );
            }

            if (!is_null($filters['date_to'] ?? null)) {
                $appointments->whereDate(
                    'appointment_summary.last_appointment',
                    '<=',
                    $filters['date_to']
                );
            }

            if (!is_null($filters['has_upcoming_appointment'] ?? null)) {

                if ($filters['has_upcoming_appointment']) {
                    // Only patients with an upcoming appointment
                    $appointments->whereNotNull('appointment_summary.upcoming_appointment');
                } else {
                    // Only patients without an upcoming appointment
                    $appointments->whereNull('appointment_summary.upcoming_appointment');
                }
            }
        }
        if ($search) {
            $appointments->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.phone', 'like', "%{$search}%")
                    ->orWhere('users.area', 'like', "%{$search}%");
            });
        }
    
        $patients = $appointments->select(self::getSelects())
                        ->groupBy('patients.id')
                        ->paginate($perPage);

        $flags = self::getFlagsData($patients, $user->doctor->id);
        

        $patients->transform(function ($patient) use ($flags) {
            $patient->avatar = $patient->avatar
                ? asset('storage/' . $patient->avatar)
                : null;

            $patient->flags = $flags->get($patient->id, collect())->values();


            return $patient;
        });

        return $patients;
    }

    private static function getSelects()
    {
        return [
            'patients.id as id',
            'users.name as name',
            'users.phone as phone',
            'users.email as email',
            'users.image as avatar',
            'users.age as age',
            'users.area as area',
            DB::raw('DATE(appointment_summary.last_appointment) as last_appointment_date'),
            DB::raw('TIME(appointment_summary.last_appointment) as last_appointment_time'),
            DB::raw('DATE(appointment_summary.upcoming_appointment) as upcoming_appointment_date'),
            DB::raw('TIME(appointment_summary.upcoming_appointment) as upcoming_appointment_time'),
        ];
    }

    private static function getAuthData(int $userId)
    {
        $user = User::where('id', $userId)->first();

        if($user->type == UserType::DOCTOR){
            $appointmentSummary = self::getAppintmentSummarData($user->doctor->id);

            return Patient::leftJoin('appointments', 'patients.id', '=', 'appointments.patient_id')
                        ->leftJoin('users', 'patients.user_id', '=', 'users.id')
                        ->leftJoinSub($appointmentSummary, 'appointment_summary', function ($join) {
                            $join->on('patients.id', '=', 'appointment_summary.patient_id');
                        })
                        ->where('appointments.doctor_id', $user->doctor->id);
        }
        else{
            return null;
        }
    }


    private static function getAppintmentSummarData(int $doctorId)
    {
        $appointmentSummary = Appointment::select(
            'patient_id',
            DB::raw('MAX(CASE WHEN date < NOW() THEN date END) as last_appointment'),
            DB::raw('MIN(CASE WHEN date >= NOW() THEN date END) as upcoming_appointment')
        )
        ->where('doctor_id', $doctorId)
        ->groupBy('patient_id');

        return $appointmentSummary;
    }

    private static function getFlagsData($patients,$doctorId)
    {
        return DB::table('flag_patient')
            ->join('flags', 'flags.id', '=', 'flag_patient.flag_id')
            ->where('flag_patient.doctor_id', $doctorId)
            ->whereIn('flag_patient.patient_id', $patients->pluck('id'))
            ->select(
                'flag_patient.patient_id',
                'flags.id',
                'flags.name',
                'flags.color',
                'flags.description'
            )
            ->get()
            ->groupBy('patient_id');
    }

}
