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
        $data = self::getAuthData($userId);
        return $data->get();
        // $user = User::where('id', $userId)->select(self::getSelects())->first();
        // $user->image = $user->image ? asset('storage/' . $user->image) : null;
        // return $user;
    }

    private static function getSelects()
    {
        return [
            'id',
            'name',
            'image',
            'email',
            'phone',
            'type',
        ];
    }

    private static function getAuthData(int $userId)
    {
        $user = User::where('id', $userId)->first();

        if($user->type == UserType::DOCTOR){
            return GetDoctorPatients::execute($userId);
        }
        else{
            return null;
        }
    }

}
