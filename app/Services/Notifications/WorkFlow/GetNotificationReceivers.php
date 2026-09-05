<?php

namespace App\Services\Notifications\WorkFlow;

use App\Models\Patient;
use App\Models\User;
use App\Services\Clinics\GetClinicPriviligedUsers;
use Illuminate\Database\Eloquent\Model;

class GetNotificationReceivers
{
    public static function execute(User $user, int $clinicId, $notification, Model $model)
    {
        $receivers = collect();

        if ($notification->notifiesPatient()) {
            $patient = Patient::find($model->patient_id);

            if ($patient) {
                $receivers->push($patient->user);
            }
        }

        if ($notification->notifiesClinic()) {
            $receivers = $receivers->merge(
                GetClinicPriviligedUsers::execute($clinicId, $notification->permission())
            );
        }

        return $receivers
            ->unique('id')
            ->reject(fn ($receiver) => $receiver->id === $user->id)
            ->values();
    }
}