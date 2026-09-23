<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\ExecutionRouter;
use App\Services\Doctors\GetActivePlan;
use App\APIServices\WhatsApp\Services\FormatPLanToMessage;

class MainMenu
{
    private const MANAGE_BOOKINGS = 'manage_bookings';
    private const WEEKLY_SCHEDULE = 'weekly_schedule';
    private const CLINIC_LOCATION = 'clinic_location';
    private const ABOUT_DOCTOR = 'about_doctor';
    private const SUBMIT_FEEDBACK = 'submit_feedback';

    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $conversation->update([
            'state' => ConversationState::MAIN_MENU,
        ]);

        $userName = $conversation->user->name;

        $greeting = $userName
            ? "أهلا {$userName}"
            : "أهلا بك";

        return SendMessage::list(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $greeting . "\nفضلا اختار من القائمة",
            'القائمة الرئيسية',
            [
                [
                    'id' => self::MANAGE_BOOKINGS,
                    'title' => 'إدارة حجوزاتي',
                ],
                [
                    'id' => self::WEEKLY_SCHEDULE,
                    'title' => 'مواعيد العيادة',
                ],
                [
                    'id' => self::CLINIC_LOCATION,
                    'title' => 'موقع العيادة',
                ],
                [
                    'id' => self::ABOUT_DOCTOR,
                    'title' => 'عن الدكتور',
                ],
                [
                    'id' => self::SUBMIT_FEEDBACK,
                    'title' => 'تسجيل رأي او شكوي',
                ],
            ]
        );
    }

    public static function handleResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        $doctor = $account->doctor;
        $clinic = $doctor->clinic;
        if ($message['type'] !== 'interactive') {
            return self::execute($conversation, $message);
        }

        switch ($message['value']) {
            
            case self::MANAGE_BOOKINGS:
                $conversation->update([
                    'state' => ConversationState::MANAGE_APPOINTMENT,
                ]);

                ManageAppointment::execute($conversation, $message);
                return;

            case self::WEEKLY_SCHEDULE:
                $activePlan = GetActivePlan::execute($doctor);

                $messageText = FormatPLanToMessage::execute($activePlan);

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    $messageText,
                );
                return;

            case self::CLINIC_LOCATION:
                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    $clinic->location_link,
                );
                return;

            case self::ABOUT_DOCTOR:
                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    $doctor->description,
                );
                return;

            case self::SUBMIT_FEEDBACK:
                $conversation->update([
                    'state' => ConversationState::SUBMIT_FEEDBACK,
                ]);

                return SubmitFeedback::execute($conversation, $message);
        }

        return self::execute($conversation, $message);
    }
}