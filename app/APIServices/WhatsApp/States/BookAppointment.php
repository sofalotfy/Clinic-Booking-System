<?php

namespace App\APIServices\WhatsApp\States;

use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use App\APIServices\WhatsApp\SendMessage;
use App\APIServices\WhatsApp\States\BookSlot;
use App\Enums\ConversationState;
use App\Models\Day;
use App\Models\DoctorWhatsAppAccount;
use App\Services\DaysInstances\Retrievals\GetAvailableDays;
use App\Services\TemplatePlans\Checks\CheckAvailability;

class BookAppointment
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if(!$conversation->user->name){
            $conversation->update([
                'state' => ConversationState::INFO_INQUIRY,
                'data' => array_merge(
                    $conversation->data ?? [],
                    ['callStack' => array_merge(
                            [ConversationState::BOOK_APPOINTMENT],
                            $conversation->data['callStack'] ?? []
                        )
                    ]
                ),
            ]);

            return InfoInquiry::execute($conversation, $message);
        }

        $days = GetAvailableDays::execute($account->doctor_id);

        SendMessage::list(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'من فضلك اختر يوم',
            'اختر يوم',
            collect($days)->map(function ($day) {
                return [
                    'id' => $day['id'],
                    'title' => ArabicDateFormatter::format(
                        Carbon::parse($day['date']),
                        includeTime: false
                    ),
                    'description' => $day['note'],
                ];
            })->toArray(),
            'ايام متاحه',
            'اخر 7 ايام'
        );
    }


    public static function handleResponse($conversation, $message)
    {
        
        
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'interactive') {
            return self::invalidResponse($conversation, $message);
        }

        $availableDayIds = collect(GetAvailableDays::execute($account->doctor_id))
            ->pluck('id')
            ->all();

        if (!in_array((int) $message['value'], $availableDayIds, true)) {
            return self::invalidResponse($conversation, $message);
        }

        $day = Day::where('doctor_id', $account->doctor_id)->find($message['value']);

        if (!$day) {
            return self::invalidResponse($conversation, $message);
        }

        if(CheckAvailability::execute($day)){
            $conversation->update([
                'state' => ConversationState::BOOK_SLOT,
                'data' => array_merge(
                    $conversation->data ?? [],
                    ['selected_day' => $day->id],
                ),
            ]);

            return BookSlot::execute($conversation, $message); 
        }else{
            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'عذرا، لا يوجد مواعيد متاحه لهذا اليوم، من فضلك اختر يوم اخر'
            );

            return self::execute($conversation, $message);
        }        
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
            'هذا الرد غير صالح، فضلا اختر أحد الخيارات المتاحة',
        );

        return self::execute($conversation, $message);
    }
}