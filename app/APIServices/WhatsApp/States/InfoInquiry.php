<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;

class InfoInquiry
{
    private const STEP_NAME = 'name';
    private const STEP_AGE = 'age';
    private const STEP_ADDRESS = 'address';

    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $step = $conversation->step ?? self::STEP_NAME;

        if (!$conversation->step) {
            $conversation->update([
                'step' => self::STEP_NAME,
            ]);
        }

        return match ($step) {
            self::STEP_NAME => SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'Please enter your name.',
            ),

            self::STEP_AGE => SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'Please enter your age.',
            ),

            self::STEP_ADDRESS => SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'Please enter your address.',
            ),
        };
    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'text') {
            return self::execute($conversation, $message);
        }

        $data = $conversation->data ?? [];

        switch ($conversation->step) {

            case self::STEP_NAME:

                $data['name'] = $message['value'];

                $conversation->update([
                    'data' => $data,
                    'step' => self::STEP_AGE,
                ]);

                return self::execute($conversation, $message);

            case self::STEP_AGE:

                if (!is_numeric($message['value'])) {
                    return SendMessage::text(
                        $account->phone_number_id,
                        $account->access_token,
                        $message['from'],
                        'Please enter a valid age.',
                    );
                }

                $data['age'] = (int) $message['value'];

                $conversation->update([
                    'data' => $data,
                    'step' => self::STEP_ADDRESS,
                ]);

                return self::execute($conversation, $message);

            case self::STEP_ADDRESS:

                $data['address'] = $message['value'];

                $conversation->update([
                    'data' => $data,
                    'step' => null,
                    'state' => ConversationState::INFO_CONFIRMATION,
                ]);

                return InfoConfirmation::execute($conversation, $message);

            default:

                $conversation->update([
                    'step' => self::STEP_NAME,
                ]);

                return self::execute($conversation, $message);
        }
    }
}