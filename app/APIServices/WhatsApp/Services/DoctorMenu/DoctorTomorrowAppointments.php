<?php

namespace App\APIServices\WhatsApp\Services\DoctorMenu;

use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\States\AdminMenu;
use App\Models\Day;
use App\Services\Appointments\Retrievals\ListAppointments;
use App\APIServices\WhatsApp\SendMessage;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use App\Models\DoctorWhatsAppAccount;

class DoctorTomorrowAppointments
{
    public static function execute(WhatsAppConversation $conversation)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        $doctor = $account->doctor;
        $user = $doctor->user;

        $day = Day::where('date', Carbon::tomorrow()->toDateString())
            ->where('doctor_id', $doctor->id)
            ->active()
            ->first();

        if (!$day) {
            $messageText = "لا يوجد يوم عمل مسجل لغدٍ.";
        } else {
            $appointments = ListAppointments::execute($user, [
                'date_from' => Carbon::tomorrow(),
                'date_to' => Carbon::tomorrow(),
            ])
            ->active()
            ->orderBy('date', 'asc')
            ->get();
            
            $start = Carbon::parse($day->date . ' ' . $day->start_time);
            $end = Carbon::parse($day->date . ' ' . $day->end_time);

            $totalSlots = intdiv(
                $start->diffInMinutes($end),
                $day->appointment_duration
            );
            
            $availableSlots = max(0, ($totalSlots + $day->queue_length) - $appointments->count());

            $arabicAppointmentsCount = ArabicDateFormatter::toArabicDigits($appointments->count());
            $arabicAvailableSlots = ArabicDateFormatter::toArabicDigits($availableSlots);
            
            if ($appointments->isNotEmpty()) {
                $firstAppointment = $appointments->first();
                $timeString = substr($firstAppointment->date, 11, 5);
                $firstAppointmentTime = ArabicDateFormatter::formatTime($timeString);
                
                $messageText = "لديك غدا {$arabicAppointmentsCount} مواعيد مؤكدة و {$arabicAvailableSlots} مواعيد متاحة للحجز\n" .
                               "أول موعد غدا الساعة {$firstAppointmentTime}\n" .
                               "العودة إلى القائمة الرئيسية";
            } else {
                $messageText = "لديك غدا {$arabicAppointmentsCount} مواعيد مؤكدة و {$arabicAvailableSlots} مواعيد متاحة للحجز\n" .
                               "العودة إلى القائمة الرئيسية";
            }
        }

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            trim($messageText)
        );

        AdminMenu::execute($conversation, []);
    }
}
