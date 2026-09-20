<?php

namespace App\APIServices\WhatsApp\Services\DoctorMenu;

use App\Models\WhatsAppConversation;
use App\Services\Appointments\Retrievals\ListAppointments;
use App\Services\Appointments\Modifications\CancelAppointment;
use App\APIServices\WhatsApp\SendMessage;
use Carbon\Carbon;

class CancelTodayAppointments
{
    public static function execute(WhatsAppConversation $conversation)
    {
        $account = $conversation->doctorWhatsAppAccount;
        $doctor = $account->doctor;
        $user = $doctor->user;

        $appointments = ListAppointments::execute($user, [
            'date_from' => Carbon::today(),
            'date_to' => Carbon::today(),
        ])->select('appointments.*')->get();

        if ($appointments->isEmpty()) {
            $messageText = "لا يوجد مواعيد مسجلة لليوم لإلغائها.";
        } else {
            foreach ($appointments as $appointment) {
                CancelAppointment::execute($user, $appointment);
            }
            $messageText = "تم إلغاء جميع مواعيد اليوم بنجاح.";
        }

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            $messageText
        );
    }
}
