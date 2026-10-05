<?php

namespace App\Services\FeedBacks;

use App\Models\FeedBack;
use App\Models\User;
use App\Models\Clinic;
use App\Enums\NotificationEnum;
use App\Services\Notifications\NotificationManager;

class CreateFeedBack
{
    public static function execute(User $user, Clinic $clinic, string $message)
    {
        $feedback = FeedBack::create([
            'message' => $message,
            'doctor_id' => $clinic->doctor_id,
            'user_id' => $user->id,
        ]);

        NotificationManager::execute(
            $user,
            $feedback->doctor_id,
            NotificationEnum::PATIENT_FEEDBACK,
            $feedback
        );

        return $feedback;
    }
}
