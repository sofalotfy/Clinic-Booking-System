<?php

namespace App\Services\FeedBacks;

use App\Models\FeedBack;
use App\Models\User;
use App\Models\Clinic;

class CreateFeedBack
{
    public static function execute(User $user, Clinic $clinic, string $message)
    {
        return FeedBack::create([
            'message' => $message,
            'doctor_id' => $clinic->doctor_id,
            'user_id' => $user->id,
        ]);
    }
}
