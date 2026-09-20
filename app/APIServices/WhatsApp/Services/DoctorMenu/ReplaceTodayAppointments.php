<?php

namespace App\APIServices\WhatsApp\Services\DoctorMenu;

use App\Models\WhatsAppConversation;
use App\Models\Day;
use App\Services\DaysInstances\Modifications\TransferDay;
use App\APIServices\WhatsApp\SendMessage;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

class ReplaceTodayAppointments
{
    public static function execute(WhatsAppConversation $conversation)
    {
        $account = $conversation->doctorWhatsAppAccount;
        $doctor = $account->doctor;
        $user = $doctor->user;

        $todayDay = Day::where('doctor_id', $doctor->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if (!$todayDay) {
            $messageText = "لا يوجد جدول عمل لليوم لاستبداله.";
        } else {
            $nextDate = Carbon::tomorrow();
            while (Day::where('doctor_id', $doctor->id)->whereDate('date', $nextDate)->exists()) {
                $nextDate->addDay();
            }

            TransferDay::execute($user, $todayDay, $nextDate->toDateString());
            
            $arabicDate = ArabicDateFormatter::format($nextDate, false);
            $messageText = "تم استبدال مواعيد اليوم ونقلها إلى {$arabicDate} بنجاح.";
        }

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            $messageText
        );
    }
}
