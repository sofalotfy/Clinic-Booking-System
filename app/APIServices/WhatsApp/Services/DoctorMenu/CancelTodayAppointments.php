<?php

namespace App\APIServices\WhatsApp\Services\DoctorMenu;

use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\States\AdminMenu;
use App\Services\DaysInstances\Modifications\CancelDay;
use App\APIServices\WhatsApp\SendMessage;
use Carbon\Carbon;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Day;

class CancelTodayAppointments
{
    public static function execute(WhatsAppConversation $conversation)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        $doctor = $account->doctor;
        $user = $doctor->user;

        $day = Day::where('doctor_id', $doctor->id)->where('date', Carbon::today())->first();

        if (!$day || $day->appointments()->active()->count() == 0) {
            $messageText = "لا يوجد مواعيد مسجلة لليوم لإلغائها.";
        } else {
            CancelDay::execute($user, $day);
            $messageText = "تم الغاء حجوزات اليوم وإرسال التنبيهات لجميع المواعيد\n" .
                           "العودة إلى القائمة الرئيسية";
        }

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            $messageText
        );

        AdminMenu::execute($conversation, []);
    }
}
