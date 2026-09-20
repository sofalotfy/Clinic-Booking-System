<?php

namespace App\APIServices\WhatsApp\Services\DoctorMenu;

use App\Models\WhatsAppConversation;
use App\Services\Appointments\Retrievals\ListAppointments;
use App\APIServices\WhatsApp\SendMessage;
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
        $user = $doctor->user;

        $appointments = ListAppointments::execute($user, [
            'date_from' => Carbon::today(),
            'date_to' => Carbon::today(),
        ])->select('appointments.*', 'users.name as patient_name')->get();

        if ($appointments->isEmpty()) {
            $messageText = "لا يوجد مواعيد مسجلة لليوم.";
        } else {
            $messageText = "مواعيد اليوم:\n";
            foreach ($appointments as $appointment) {
                $timeString = substr($appointment->date, 11, 5);
                $timeFormatted = ArabicDateFormatter::formatTime($timeString);
                $patientName = $appointment->patient_name ?? 'مريض غير مسجل';
                $messageText .= "• {$patientName} الساعة {$timeFormatted}\n";
            }
        }

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            trim($messageText)
        );
    }
}
