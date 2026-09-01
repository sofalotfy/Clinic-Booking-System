<?php

namespace App\Services\Authentications;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CheckDoctorPermission
{
    public static function execute($user, string $permission, ?Model $model = null): bool
    {
        // GET ACTOR USER DOCTOR ID
        $userDoctorId = $user->clinicDoctorId();

        if ($userDoctorId === null) {
            return false;
        }

        //CHECK IF MODEL BELONGS TO DOCTOR AND USER HAS PERMISSION
        if (self::isDoctorModel($model, $userDoctorId) && self::authorizeUser($user, $userDoctorId, $permission)) {
            return true;
        }

        return false;
    }

    // CHECK IF MODEL BELONGS TO DOCTOR
    private static function isDoctorModel(?Model $model, int $doctorId): bool
    {
        if (!$model) {
            return true;
        }

        return self::getModelDoctorId($model) === $doctorId;
    }

    // GET MODEL DOCTOR ID
    private static function getModelDoctorId(Model $model): ?int
    {
        return $model->doctor_id !== null ? (int) $model->doctor_id : null;
    }

    //AUTHORIZE USER BASED ON USER TYPE 
    private static function authorizeUser(User $user, int $doctorId, string $permission): bool
    {
        if ($user->type == UserType::DOCTOR) {
            return true;
        }

        return self::checkAssistantPermission($user, $doctorId, $permission);
    }

    // CHECK ASSISTANT PERMISSION
    private static function checkAssistantPermission(User $user, int $doctorId, string $permission): bool
    {
        return $user->roles()
            ->where('doctor_id', $doctorId)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }
}