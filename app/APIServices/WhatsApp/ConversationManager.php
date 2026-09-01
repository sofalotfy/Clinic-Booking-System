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
            'payload' => $message,
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
        $patient = Patient::whereHas('user', function ($query) use ($message) {
            $query->where('users.phone', $message['from']);
        })->first();

        if (! $patient) {
            $user = User::create([
                'phone' => $message['from'],
                'type' => UserType::PATIENT,
                // Leave other fields null for now
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
            ]);
        }

        // 4. Find or create the conversation
        $conversation = WhatsAppConversation::firstOrCreate(
            [
                'doctor_whatsapp_account_id' => $doctorAccount->id,
                'phone_number' => $message['from'],
            ],
            [
                'patient_id' => $patient->id,
                'step' => null,
                'data' => ['name' =>  $patient->user->name],
            ]
        );

        // 5. Refresh activity
        $conversation->update([
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        // In ConversationManager::execute()

        // if ($message['type'] === 'text' && !empty($message['value'])) {
        //     try {
        //         $userText = $message['value'];

        //         // 1. Retrieve recent history BEFORE saving current message (e.g., last 10 messages)
        //         $history = WhatsappMessages::getHistory($conversation->id, 5);

        //         // 2. Save incoming user message
        //         $conversation->messages()->create([
        //             'role'    => 'user',
        //             'content' => $userText,
        //         ]);

        //         // 3. Context Payload
        //         $context = [
        //             'doctor_id'     => (int) ($doctorAccount->doctor_id ?? $doctorAccount->id),
        //             'doctor_name'   => $doctorAccount->doctor->user->name ?? 'Specialist',
        //             'patient_id'    => $patient->id ?? null,
        //             'patient_name'  => $patient->user->name ?? 'Patient',
        //             'patient_phone' => $message['from'],
        //         ];

        //         // 4. Request AI Completion
        //         $aiService = app(WhatsAppAiService::class);
        //         $replyText = $aiService->ask($userText, $history, $context);

        //         // 5. Save AI's response
        //         $conversation->messages()->create([
        //             'role'    => 'assistant',
        //             'content' => $replyText,
        //         ]);

        //         // 6. Send message via WhatsApp API
        //         SendMessage::text(
        //             $doctorAccount->phone_number_id,
        //             $doctorAccount->access_token,
        //             $message['from'],
        //             $replyText
        //         );

        //     } catch (\Exception $e) {
        //         \Log::error('WhatsApp AI Processing Error: ' . $e->getMessage(), [
        //             'exception' => $e
        //         ]);
        //     }
        // }

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