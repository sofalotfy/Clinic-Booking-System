<?php

namespace App\APIServices\WhatsApp\Services\DoctorMenu;

use App\Models\WhatsAppConversation;
use App\Services\Appointments\Retrievals\ListAppointments;
use App\Services\Appointments\Modifications\UpdateAppointment;
use App\APIServices\WhatsApp\SendMessage;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use App\Models\DoctorWhatsAppAccount;

class DelayClinicAppointment
{
    public static function execute(WhatsAppConversation $conversation, int $hours)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        $doctor = $account->doctor;
        $user = $doctor->user;

        $appointments = ListAppointments::execute($user, [
            'date_from' => Carbon::today(),
            'date_to' => Carbon::today(),
        ])->select('appointments.*')->get();

        if ($appointments->isEmpty()) {
            $messageText = "لا يوجد مواعيد مسجلة لليوم لتعديلها.";
        } else {
            foreach ($appointments as $appointment) {
                $newTime = Carbon::parse($appointment->date)->addHours($hours)->format('H:i');
                UpdateAppointment::execute($user, $appointment, time: $newTime);
            }
            $arabicHours = ArabicDateFormatter::toArabicDigits($hours);
            $messageText = "تم تأخير جميع مواعيد اليوم بمقدار {$arabicHours} ساعة بنجاح.";
        }

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            $messageText
        );
    }
}
