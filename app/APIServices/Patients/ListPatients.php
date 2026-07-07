<?php

namespace App\APIServices\Patients;

use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Enums\UserType;
use Illuminate\Support\Facades\DB;


class ListPatients
{
    public static function execute(int $userId)
    {
        $user = User::findOrFail($userId);
        
        $appointments = self::getAuthData($userId);

        if(is_null($appointments)){
            return null;
        }

    
        $patients = $appointments->select(self::getSelects())
                        ->groupBy('patients.id')
                        ->get();

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
            'users.address as address',
            'appointment_summary.last_appointment',
            'appointment_summary.upcoming_appointment',
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
