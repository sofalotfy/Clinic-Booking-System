<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\Services\DoctorMenu\AdvanceClinicAppointment;
use App\APIServices\WhatsApp\Services\DoctorMenu\CancelTodayAppointments;
use App\APIServices\WhatsApp\Services\DoctorMenu\DelayClinicAppointment;
use App\APIServices\WhatsApp\Services\DoctorMenu\DoctorTodayAppointments;
use App\APIServices\WhatsApp\Services\DoctorMenu\DoctorTomorrowAppointments;
use App\APIServices\WhatsApp\States\ChooseReplaceDay;

class AdminMenu
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $doctor = $account->doctor;

        $message =
            "اهلا دكتور {$doctor->user->name}\n"
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
            '',
            'مواعيد العيادة'
        );
    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        if ($message['type'] !== 'interactive') {
            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $conversation->phone_number,
                'عفواً، استجابة غير صحيحة. يرجى اختيار أحد الخيارات المتاحة.'
            );
            return self::execute($conversation, $message);
        }

        $result = match ($message['value']) {
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
                ChooseReplaceDay::execute($conversation, $message),

            default => null,
        };

        if ($result === null) {
            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $conversation->phone_number,
                'عفواً، خيار غير صحيح. يرجى اختيار أحد الخيارات المتاحة.'
            );
            return self::execute($conversation, $message);
        }

        return $result;
    }
}
