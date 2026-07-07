<?php

namespace App\APIServices\Patients;

use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Enums\UserType;


class ListPatients
{
    public static function execute(int $userId)
    {
        $appointments = self::getAuthData($userId);

        if(is_null($appointments)){
            return null;
        }


        $patients = $appointments->select(self::getSelects())
                        ->groupBy('patients.id')
                        ->get();

        $patients->transform(function ($patient) {
            $patient->avatar = $patient->avatar
                ? asset('storage/' . $patient->avatar)
                : null;
            return $patient;
        });

        return $patients;
    }

    private static function getSelects()
    {
        return [
            'users.id as id',
            'users.name as name',
            'users.phone as phone',
            'users.email as email',
            'users.image as avatar',
            'users.age as age',
            'users.address as address',
        ];
    }

    private static function getAuthData(int $userId)
    {
        $user = User::where('id', $userId)->first();

        if($user->type == UserType::DOCTOR){
            return Patient::leftJoin('appointments', 'patients.id', '=', 'appointments.patient_id')
                        ->leftJoin('users', 'patients.user_id', '=', 'users.id')
                        ->where('appointments.doctor_id', $user->doctor->id);
        }
        else{
            return null;
        }
    }

}
