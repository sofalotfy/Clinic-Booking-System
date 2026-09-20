<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Models\WhatsAppConversation;
use App\Services\Appointments\Retrievals\ListAppointments;
use App\APIServices\WhatsApp\Services\DoctorMenu\AdvanceClinicAppointment;
use App\APIServices\WhatsApp\Services\DoctorMenu\CancelTodayAppointments;
use App\APIServices\WhatsApp\Services\DoctorMenu\DelayClinicAppointment;
use App\APIServices\WhatsApp\Services\DoctorMenu\DoctorTodayAppointments;
use App\APIServices\WhatsApp\Services\DoctorMenu\DoctorTomorrowAppointments;
use App\APIServices\WhatsApp\Services\DoctorMenu\ReplaceTodayAppointments;

class AdminMenu
{
    public static function execute($conversation, $message)
    {
        $account = $conversation->doctorWhatsAppAccount;
        $doctor = $account->doctor;

        $message =
            "اهلا دكتور {$doctor->name}\n"
            . "روزة تتمنى لك يوم جميل\n"
            . "كيف استطيع ان اساعدك";

        return SendMessage::list(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            $message,
            'عرض الخيارات',
            [
                [
                    'id' => 'doctor_today_appointments',
                    'title' => 'مواعيد اليوم',
                    'description' => 'عرض مواعيد اليوم',
                ],
                [
                    'id' => 'doctor_tomorrow_appointments',
                    'title' => 'مواعيد غدا',
                    'description' => 'عرض مواعيد غدا',
                ],
                [
                    'id' => 'doctor_advance_one_hour',
                    'title' => 'تقديم الموعد ساعة',
                    'description' => 'تقديم موعد العيادة ساعة',
                ],
                [
                    'id' => 'doctor_advance_two_hours',
                    'title' => 'تقديم الموعد ساعتين',
                    'description' => 'تقديم موعد العيادة ساعتين',
                ],
                [
                    'id' => 'doctor_delay_one_hour',
                    'title' => 'تأخير الموعد ساعة',
                    'description' => 'تأخير موعد العيادة ساعة',
                ],
                [
                    'id' => 'doctor_delay_two_hours',
                    'title' => 'تأخير الموعد ساعتين',
                    'description' => 'تأخير موعد العيادة ساعتين',
                ],
                [
                    'id' => 'doctor_cancel_today',
                    'title' => 'إلغاء مواعيد اليوم',
                    'description' => 'إلغاء حجوزات اليوم',
                ],
                [
                    'id' => 'doctor_replace_today',
                    'title' => 'استبدال مواعيد اليوم',
                    'description' => 'استبدال مواعيد اليوم بيوم آخر',
                ],
            ],
            'خيارات الطبيب',
            'مواعيد العيادة'
        );
    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        if ($message['type'] !== 'interactive') {
            return self::execute($conversation, $message);
            
            $conversation->update([
                'state' => ConversationState::AI,
            ]);

            return ExecutionRouter::execute($conversation, $message);
        }

        return match ($message['value']) {
            'doctor_today_appointments' =>
                DoctorTodayAppointments::execute($conversation),

            'doctor_tomorrow_appointments' =>
                DoctorTomorrowAppointments::execute($conversation),

            'doctor_advance_one_hour' =>
                AdvanceClinicAppointment::execute($conversation, 1),

            'doctor_advance_two_hours' =>
                AdvanceClinicAppointment::execute($conversation, 2),

            'doctor_delay_one_hour' =>
                DelayClinicAppointment::execute($conversation, 1),

            'doctor_delay_two_hours' =>
                DelayClinicAppointment::execute($conversation, 2),

            'doctor_cancel_today' =>
                CancelTodayAppointments::execute($conversation),

            'doctor_replace_today' =>
                ReplaceTodayAppointments::execute($conversation),

            default => null,
        };
    }
}
