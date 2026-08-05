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

        // 6. Direct AI Execution with Memory
        if ($message['type'] === 'text' && !empty($message['value'])) {
            try {
                $userText = $message['value'];

                // A. Retrieve recent history for this conversation (last 10 messages)
                $history = WhatsappMessages::getHistory($conversation->id, 10);

                // B. Save incoming user message
                $conversation->messages()->create([
                    'role' => 'user',
                    'content' => $userText,
                ]);

                // Build context array from database models
                $context = [
                    'doctor_id'     => $doctorAccount->doctor_id ?? $doctorAccount->id,
                    'doctor_name'   => $doctorAccount->doctor->name ?? 'Specialist',
                    'patient_id'    => $patient->id ?? null,
                    'patient_name'  => $patient->user->name ?? 'Patient',
                    'patient_phone' => $message['from'],
                ];

                // C. Call AI with user text + past history
                $aiService = app(WhatsAppAiService::class);
                $replyText = $aiService->ask($userText, $history, $context);

                // D. Save AI's response to history
                $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $replyText,
                ]);

                // E. Send reply back to user via WhatsApp
                SendMessage::text(
                    $doctorAccount->phone_number_id,
                    $doctorAccount->access_token,
                    $message['from'],
                    $replyText
                );

            } catch (\Exception $e) {
                \Log::error('WhatsApp AI Processing Error: ' . $e->getMessage());
            }
        }

        // // 6. Hand off to the router
        // ConversationRouter::execute(
        //     $conversation,
        //     $message
        // );
    }

    private static function extractMessage(array $payload): array
    {
        $value = $payload['entry'][0]['changes'][0]['value'];
        $message = $value['messages'][0];

        return [
            'phone_number_id' => $value['metadata']['phone_number_id'],
            'from'            => $message['from'],
            'type'            => $message['type'],
            'value'           => match ($message['type']) {
                'text' => $message['text']['body'] ?? null,

                'interactive' => match ($message['interactive']['type'] ?? null) {
                    'button_reply' => $message['interactive']['button_reply']['id'] ?? null,
                    'list_reply'   => $message['interactive']['list_reply']['id'] ?? null,
                    default        => null,
                },

                'button' => $message['button']['payload'] ?? null,

                default => null,
            },

            'message_id' => $message['id'],
        ];
    }
}