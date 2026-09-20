<?php

namespace App\APIServices\WhatsApp\Services\DoctorMenu;

use App\Models\WhatsAppConversation;
use App\Services\Appointments\Retrievals\ListAppointments;
use App\APIServices\WhatsApp\SendMessage;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

class DoctorTomorrowAppointments
{
    public static function execute(WhatsAppConversation $conversation)
    {
        $account = $conversation->doctorWhatsAppAccount;
        $doctor = $account->doctor;
        $user = $doctor->user;

        $appointments = ListAppointments::execute($user, [
            'date_from' => Carbon::tomorrow(),
            'date_to' => Carbon::tomorrow(),
        ])->select('appointments.*', 'users.name as patient_name')->get();

        if ($appointments->isEmpty()) {
            $messageText = "لا يوجد مواعيد مسجلة لغدٍ.";
        } else {
            $messageText = "مواعيد الغد:\n";
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
