<?php

namespace App\Services\Notifications\Doctor;

use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Notification;
use App\APIServices\WhatsApp\SendMessage;

class UrgentNotify
{
    public static function execute(Doctor $doctor, $type, $title, $text)
    {
        $account = DoctorWhatsAppAccount::where('doctor_id', $doctor->id)->first();
        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            '201013292553',// $doctor->user->phone,
            $title,
            $text
        );

        return Notification::create([
            'user_id' => $doctor->user_id,
            'type' => $type,
            'title' => $title,
            'text' => $text,
        ]);        
    }
}