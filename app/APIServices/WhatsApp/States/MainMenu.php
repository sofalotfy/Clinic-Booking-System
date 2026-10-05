<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Doctors\GetActivePlan;
use App\Services\Assistants\GetAssistantsContacts;
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
            $greeting . "\nفضلا اختر من القائمة",
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
            return self::invalidResponse($conversation, $message);
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

                self::execute($conversation, $message);
                return;

            case self::CLINIC_LOCATION:
                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    self::clinicInfo($doctor, $clinic),
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

        return self::invalidResponse($conversation, $message);
    }

    private static function clinicInfo($doctor, $clinic): string
    {
        $lines = ['*بيانات العيادة*'];

        if ($clinic?->location_link) {
            $lines[] = "\n*الموقع على خرائط جوجل*\n" . $clinic->location_link;
        }

        if ($clinic?->address) {
            $lines[] = "\n*العنوان*\n" . $clinic->address;
        }

        $contacts = self::contactNumbers($doctor);

        if ($contacts) {
            $lines[] = "\n*ارقام التواصل*";

            foreach ($contacts as $contact) {
                $lines[] = "{$contact['name']}: {$contact['phone']}";
            }

            $lines[] = "\n_ملاحظة: الارقام تعمل فقط خلال اوقات العمل الرسمية للعيادة_";
        }

        return implode("\n", $lines);
    }

    private static function contactNumbers($doctor): array
    {
        $contacts = array_merge(
            [
                [
                    'name' => $doctor->user?->name ?? 'الدكتور',
                    'phone' => $doctor->user?->phone,
                ],
            ],
            GetAssistantsContacts::execute($doctor)
        );

        return array_values(array_filter(
            $contacts,
            fn ($contact) => ! empty($contact['name']) && ! empty($contact['phone'])
        ));
    }

    private static function invalidResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'نأسف لعدم تفهمنا لرسالتك 
فضلا اختر أحد الخيارات المتاحة',
        );

        return self::execute($conversation, $message);
    }
}