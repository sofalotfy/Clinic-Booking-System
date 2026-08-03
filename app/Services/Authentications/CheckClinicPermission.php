<?php

namespace App\Services\Authentications;

use Illuminate\Database\Eloquent\Model;
use App\Enums\UserType;

class CheckClinicPermission
{
    public static function execute($user, string $permission, ?Model $model = null): bool {
        // Doctor
        if ($user->type == UserType::DOCTOR) {
            if ($model == null) {
                return true;
            }

            return $model->doctor_id == $user->doctor->id;
        }

        // Assistant
        if ($user->type == UserType::ASSISTANT) {

            if (! $user->can($permission)) {
                return false;
            }

            if ($model == null) {
                return true;
            }

            return $model->doctor_id == $user->assistant->doctor_id;
        }

        return false;
    }
}