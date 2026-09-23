<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\Retrievals\GetUpComingAppointment;
use App\Support\ArabicDateFormatter;
use App\APIServices\WhatsApp\States\InfoInquiry;
use App\APIServices\WhatsApp\ExecutionRouter;
use Carbon\Carbon;

class ManageAppointment
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $appointment = GetUpComingAppointment::execute(
            $conversation->patient()?->id,
            $account->doctor_id
        );

        $conversation->update([
            'state' => ConversationState::MANAGE_APPOINTMENT,
        ]);

        if ($appointment) {

            $conversation->update([
                'data' => array_merge(
                    $conversation->data ?? [],
                    [
                        'appointment_id' => $appointment->id,
                    ]
                ),
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

    private static function sendAppointmentMenu($account, $conversation, $message, $appointment)
    {
        $userName = $conversation->user->name;

        $dateTime = Carbon::parse($appointment->date . ' ' . $appointment->start_time);
        $formattedDate = ArabicDateFormatter::format($dateTime);

        $greeting = $userName
            ? "أهلا {$userName}\n"
            : "أهلا بك\n";

        $text = $greeting . "لديك موعد يوم {$formattedDate}";

        SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $text,
            [
                [
                    'id' => 'reschedule_appointment',
                    'title' => 'تعديل الموعد',
                ],
                [
                    'id' => 'cancel_appointment',
                    'title' => 'إلغاء الموعد',
                ],
                [
                    'id' => 'update_profile',
                    'title' => 'تحديث البيانات',
                ],
            ]
        );
    }

    private static function sendBookingMenu($account, $conversation, $message)
    {
        $userName = $conversation->user->name;

        $text = $userName
            ? "أهلا {$userName}\nليس لديك مواعيد مسجلة حاليا"
            : "اهلا بك\nنسعد بخدمتك ... فضلا قم بإختيار أحد الخيارات";

        SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $text,
            [
                [
                    'id' => 'book_appointment',
                    'title' => 'حجز موعد',
                ],
                [
                    'id' => 'update_profile',
                    'title' => 'تحديث البيانات',
                ],
                [
                    'id' => 'end_conversation',
                    'title' => 'إنهاء المحادثة',
                ],
            ]
        );
    }

    public static function handleResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'interactive') {
            return self::execute($conversation, $message);
        }

        switch ($message['value']) {

            case 'reschedule_appointment':
                $conversation->update([
                    'state' => ConversationState::BOOK_APPOINTMENT,
                ]);

                return BookAppointment::execute($conversation, $message);

            case 'cancel_appointment':
                $conversation->update([
                    'state' => ConversationState::CANCEL_APPOINTMENT,
                ]);

                return CancelAppointment::execute($conversation, $message);

            case 'book_appointment':
                $conversation->update([
                    'state' => ConversationState::BOOK_APPOINTMENT,
                ]);

                return BookAppointment::execute($conversation, $message);

            case 'update_profile':
                $conversation->update([
                    'state' => ConversationState::INFO_INQUIRY,
                    'data' => array_merge(
                        $conversation->data ?? [],
                        [
                            'callStack' => array_merge(
                                [ConversationState::MANAGE_APPOINTMENT],
                                $conversation->data['callStack'] ?? [],
                            ),
                        ]
                    ),
                ]);

                return InfoInquiry::execute($conversation, $message);

            case 'end_conversation':
                $conversation->delete();

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'شكرا لتواصلك معنا ونسعد بكم دوما',
                );

                return;
        }

        return self::execute($conversation, $message);
    }
}