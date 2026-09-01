<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\SmartBookAppointment;
use App\Models\Day;
use App\APIServices\Days\CheckAvailability;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use App\APIServices\WhatsApp\ExecutionRouter;

class ConfirmBooking
{

    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $day = Day::find($conversation->data['selected_day']);
        
        if(CheckAvailability::execute($day->id)){
            $state = AppointmentStatus::ACTIVE;
            $text = "هل انت متاكد من حجز الموعد في يوم {$day->date} في تمام الوقت {$conversation->data['selected_slot']}";
        }else{
            $state = AppointmentStatus::QUEUED;
            $text = "هل انت متاكد من حجز الموعد في يوم {$day->date} في قائمة الانتظار";
        }

        $conversation->update([
                'data' => array_merge(
                    $conversation->data ?? [],
                    [
                        'booking_state' => $state,
                    ]
                ),
            ]);
        
        return SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
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

                $day = Day::find($conversation->data['selected_day']);
                $date = $day->date;
                $time = CheckAvailability::execute($day->id)?$conversation->data['selected_slot']:"00:00";
                $dateTime = Carbon::parse($date . ' ' . $time);
                
                SmartBookAppointment::execute(
                    $conversation->patient,
                    $account->doctor,
                    $dateTime,
                    $day->appointment_duration,
                    $conversation->data['booking_state'],
                );
                

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'تم تاكيد حجز الموعد.',
                );

                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);

                break;

            case 'cancel':
                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);
        }

        return self::execute($conversation, $message);
    }
}