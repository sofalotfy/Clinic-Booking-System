<?php

namespace App\APIServices\WhatsApp\Services\DoctorMenu;

use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\States\AdminMenu;
use App\Models\Day;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use App\Models\DoctorWhatsAppAccount;

class DoctorTodayAppointments
{
    public static function execute(WhatsAppConversation $conversation)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        $doctor = $account->doctor;

        $day = Day::where('date', Carbon::today()->toDateString())
            ->where('doctor_id', $doctor->id)
            ->active()
            ->first();

        if (!$day) {
            $messageText = "لا يوجد يوم عمل مسجل لليوم.";
        } else {
            $appointments = $day->getAppointments();

            $arabicAppointmentsCount = ArabicDateFormatter::toArabicDigits($appointments->count());
            $arabicAvailableSlots = ArabicDateFormatter::toArabicDigits(
                $day->getEmptySlots($appointments->count())
            );

            $messageText = "لديك اليوم {$arabicAppointmentsCount} مواعيد مؤكدة و {$arabicAvailableSlots} متاحين للحجز حتى الآن\n";

            if ($appointments->isNotEmpty()) {
                $messageText .= "أول موعد اليوم الساعة {$day->getFirstAppointmentTime($appointments)}\n";
            }

            $messageText .= "العودة إلى القائمة الرئيسية";
        }

        AdminMenu::send($conversation, trim($messageText));
    }
}
