<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Day;
use App\Models\Appointment;
use App\APIServices\Days\CheckAvailability;
use App\APIServices\Appointments\SmartBookAppointment;
use Carbon\Carbon;
use App\APIServices\WhatsApp\ExecutionRouter;

class ConfirmReshedule
{

    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );



        $rescheduleType = $conversation->data['reschedule_type'] ?? null;
        $appointment = Appointment::find($conversation->data['appointment_id']);
        $newDate = $conversation->data['new_date'] ?? $appointment->date;

        if ($rescheduleType == AppointmentUpdateNotificationTypes::TRUNCATE->value) {
            $text = "عذرا، لقد تغير جدول مواعيد العيادة، ولم نعد نعمل في هذا اليوم، وتم الغاء موعدك. هل ترغب في حجز موعد جديد؟";
        } elseif ($rescheduleType == AppointmentUpdateNotificationTypes::OVERFLOW->value) {
            $text = "عذرا، لقد تغير جدول مواعيد العيادة، وتم نقل موعدك إلى قائمة الانتظار، هل ترغب في تاكيد ذلك؟";
        } elseif ($rescheduleType == AppointmentUpdateNotificationTypes::CANCEL->value) {
            $text = "عذرا، لقد تم الغاء موعدك من قبل الدكتور، هل ترغب في حجز موعد جديد؟";
        } elseif ($rescheduleType == AppointmentUpdateNotificationTypes::COLIDE->value) {
            $text = "تم نقل موعدك ليوم {$newDate}. هل ترغب في تاكيد ذلك؟";
        } elseif ($rescheduleType == AppointmentUpdateNotificationTypes::QUEUED->value) {
            $text = "تم إضافة موعدك إلى قائمة الانتظار. هل ترغب في تاكيد ذلك؟";
        } else {
            $text = "تم نقل موعدك ليوم {$newDate}. هل ترغب في تاكيد ذلك؟";
        }
            
        return SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $appointment->patient->user->phone,
            $text,
            [
                [
                    'id' => 'confirm',
                    'title' => 'تاكيد',
                ],
                [
                    'id' => 'cancel',
                    'title' => 'الغاء',
                ],
            ]
        );
    }

    public static function handleResponse($conversation, $message)
    {
        $appointment = Appointment::find($conversation->data['appointment_id']);
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'interactive') {
            $conversation->update([
                'state' => ConversationState::AI,
            ]);

            return ExecutionRouter::execute($conversation, $message);
        }
        
        switch ($message['value']) {

            case 'confirm':
                $appointment->update([
                    'isConfirmed'  =>  true,
                ]);
                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'تم تاكيد حجز الموعد'
                );

                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);

                break;

            case 'cancel':
                $appointment->delete();
                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);
        }

        return self::execute($conversation, $message);
    }
}