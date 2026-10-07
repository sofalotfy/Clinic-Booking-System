<?php

namespace App\Services\Notifications\WorkFlow;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GetNotificationReceivers
{
    public static function execute(User $user, int $clinicId, $notification, Model $model)
    {
        $receivers = collect();

        if ($notification->notifiesPatient()) {
            $patient = Patient::find($model->patient_id);

            if ($patient && $patient->user) {
                $receivers->push([
                    'user' => $patient->user,
                    'phone' => $patient->user->phone,
                    'name' => $patient->user->name,
                ]);
            }
        }

        if ($notification->notifiesClinic()) {
            $clinic = Doctor::find($clinicId)?->clinic;

            if ($clinic && $clinic->notifications_phone) {
                $receivers->push([
                    'user' => null,
                    'phone' => $clinic->notifications_phone,
                    'name' => null,
                ]);
            }
        }

        return $receivers
            ->unique('phone')
            // ->reject(fn ($receiver) => $receiver['phone'] === $user->phone)
            ->values();
    }
}
