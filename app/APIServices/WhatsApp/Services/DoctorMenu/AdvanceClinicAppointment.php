<?php

namespace App\APIServices\WhatsApp\Services\DoctorMenu;

use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\States\AdminMenu;
use App\Models\Day;
use App\Services\DaysInstances\Modifications\UpdateDay;
use App\APIServices\WhatsApp\SendMessage;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use App\Models\DoctorWhatsAppAccount;

class AdvanceClinicAppointment
{
    public static function execute(WhatsAppConversation $conversation, int $hours)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        $doctor = $account->doctor;
        $user = $doctor->user;

        $day = Day::where('date', Carbon::today()->toDateString())
            ->where('doctor_id', $doctor->id)
            ->active()
            ->first();

        if (!$day) {
            $messageText = "لا يوجد يوم عمل مسجل لليوم لتعديله.";
        } else {
            $newStartTime = Carbon::parse($day->start_time)->subHours($hours)->format('H:i:s');
            $newEndTime = Carbon::parse($day->end_time)->subHours($hours)->format('H:i:s');
            
            UpdateDay::execute($user, $day, [
                'start_time' => $newStartTime,
                'end_time' => $newEndTime,
            ]);

            $arabicHours = $hours == 1 ? "ساعة" : "ساعتين";
            $startTime = ArabicDateFormatter::formatTime(Carbon::parse($newStartTime)->format('H:i'));
            $messageText = "تم تقديم موعد العيادة اليوم {$arabicHours} لتبدأ من {$startTime}\n" .
                           "وتم التنبيه على جميع المواعيد بالتعديل و في انتظار تأكيدهم للحضور\n" .
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
