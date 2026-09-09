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
                'من فضلك ادخل اسمك.',
            ),

            self::STEP_AGE => SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'من فضلك ادخل عمرك.',
            ),

            self::STEP_ADDRESS => SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'من فضلك ادخل عنوانك.',
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

                $value = self::convertArabicNumeralsToEnglish($message['value']);

                if (!is_numeric($value) or $value < 1 or $value > 120) {
                    return SendMessage::text(
                        $account->phone_number_id,
                        $account->access_token,
                        $message['from'],
                        'من فضلك ادخل عمرك.',
                    );
                }

                $data['age'] = (int) $value;

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

    private static function convertArabicNumeralsToEnglish(string $value): string
    {
        $arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $persianNumerals = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $value = str_replace($arabicNumerals, $englishNumerals, $value);
        $value = str_replace($persianNumerals, $englishNumerals, $value);

        return trim($value);
    }
}