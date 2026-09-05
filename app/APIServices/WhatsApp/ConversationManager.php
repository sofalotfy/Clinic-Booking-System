<?php

namespace App\APIServices\WhatsApp;

use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\WhatsAppConversation;
use App\Models\User;
use App\Enums\UserType;
use App\Enums\ConversationState;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\WhatsappMessages;
use App\APIServices\WhatsApp\AI\WhatsAppAiService;
use App\Services\Patients\Creations\StorePatient;

class ConversationManager
{
    public static function execute(array $payload)
    {

        $value = $payload['entry'][0]['changes'][0]['value'] ?? [];

        if (! isset($value['messages'][0])) {
            // Ignore sent/delivered/read webhooks
            return;
        }
        \Log::info([
            'payload' => $payload,
        ]);
        // 1. Extract the message from the webhook
        $message = self::extractMessage($payload);

        \Log::info([
            'message_id' => $message['message_id'],
            'from' => $message['from'],
        ]);

        // 2. Resolve the doctor's WhatsApp account
        $doctorAccount = DoctorWhatsAppAccount::where(
            'phone_number_id',
            $message['phone_number_id']
        )->firstOrFail();

        // 3. Find the patient by the sender's phone number
        $user = User::where('phone', $message['from'])->first();

        if (!$user) {
            $user = StorePatient::execute($message['from'])->user;
        }

        // 4. Find or create the conversation
        $conversation = WhatsAppConversation::firstOrCreate(
            [
                'doctor_whatsapp_account_id' => $doctorAccount->id,
                'phone_number' => $message['from'],
            ],
            [
                'user_id' => $user->id,
                'step' => null,
                'data' => ['name' =>  $user->name],
            ]
        );

        // 5. Refresh activity
        $conversation->update([
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        // 6. Hand off to the router
        ConversationRouter::execute(
            $conversation,
            $message
        );
    }

    private static function extractMessage(array $payload): array
    {
        $value = $payload['entry'][0]['changes'][0]['value'];
        $message = $value['messages'][0];

        return [
            'phone_number_id' => $value['metadata']['phone_number_id'],
            'from' => isset($message['from'])
                ? $message['from']
                : ($message['from_user_id'] ?? null),
            'type'            => $message['type'],
            'value'           => match ($message['type']) {
                'text' => $message['text']['body'] ?? null,

                'interactive' => match ($message['interactive']['type'] ?? null) {
                    'button_reply' => $message['interactive']['button_reply']['id'] ?? null,
                    'list_reply'   => $message['interactive']['list_reply']['id'] ?? null,
                    default        => null,
                },

                'button' => $message['button']['payload'] ?? null,

                'location' => [
                    'latitude'  => $message['location']['latitude'] ?? null,
                    'longitude' => $message['location']['longitude'] ?? null,
                    'name'      => $message['location']['name'] ?? null,
                    'address'   => $message['location']['address'] ?? null,
                ],
                default => null,
            },

            'message_id' => $message['id'],
        ];
    }
}