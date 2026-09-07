<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\Retrievals\GetUpComingAppointment;
use App\APIServices\WhatsApp\States\InfoInquiry;
use App\APIServices\WhatsApp\ExecutionRouter;

class MainMenu
{
    public static function execute($conversation, $message)
    {
        $account = $conversation->doctorWhatsAppAccount;

        $appointment = GetUpComingAppointment::execute(
            $conversation->patient()?->id,
            $account->doctor_id
        );

        if ($appointment) {
            $conversation->pushData([
                'appointment_id' => $appointment->id,
            ]);

            return self::sendAppointmentMenu(
                $account,
                $conversation,
                $message,
                $appointment
            );
        }

        return self::sendBookingMenu(
            $account,
            $conversation,
            $message
        );

    }

    private static function sendAppointmentMenu($account, $conversation, $message, $appointment) {

        $greeting = $conversation->user->name
            ? "مرحبا {$conversation->user->name},\n\n"
            : '';

        
        SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $greeting . "لديك موعد قادم في يوم {$appointment->date} في تمام الوقت {$appointment->start_time}.\n\nمن فضلك اختر خيار:",
            [
                [
                    'id' => 'reschedule_appointment',
                    'title' => 'تعديل الموعد',
                ],
                [
                    'id' => 'cancel_appointment',
                    'title' => 'الغاء الموعد',
                ],
                [
                    'id' => 'update_profile',
                    'title' => 'تعديل البيانات',
                ],
            ]
        );
    }

    private static function sendBookingMenu($account, $conversation, $message)
    {
        $greeting = $conversation->user->name
            ? "Hi {$conversation->user->name},\n\n"
            : "";
        \Log::info([
            'success' => "hello",
        ]);
        SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $greeting . "انت لا تملك اي مواعيد قادمه.\n\nمن فضلك اختر خيار:",
            [
                [
                    'id' => 'book_appointment',
                    'title' => 'حجز موعد',
                ],
                [
                    'id' => 'update_profile',
                    'title' => 'تعديل البيانات',
                ],
                [
                    'id' => 'end_conversation',
                    'title' => 'انهاء المحادثة',
                ],
            ]
        );
    }

    public static function handleResponse($conversation, $message)
    {
        $account = $conversation->doctorWhatsAppAccount;

        if ($message['type'] !== 'interactive') {
            return $conversation->changeState(ConversationState::AI, $message);
        }

        switch ($message['value']) {

            case 'reschedule_appointment':
                return $conversation->startFlow(
                    ConversationState::BOOK_APPOINTMENT,
                    $message
                );

            case 'cancel_appointment':
                return $conversation->startFlow(
                    ConversationState::CANCEL_APPOINTMENT,
                    $message
                );

            case 'book_appointment':
                return $conversation->startFlow(
                    ConversationState::BOOK_APPOINTMENT,
                    $message
                );

            case 'update_profile':
                return $conversation->startFlow(
                    ConversationState::INFO_INQUIRY,
                    $message
                );

            case 'end_conversation':
                $conversation->end();

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'شكرا لاستخدام خدماتنا',
                );

                return;
        }
        return self::execute($conversation, $message);
    }
}