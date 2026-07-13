<?php

namespace App\APIServices\WhatsApp;

use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\WhatsAppConversation;
use App\Models\User;
use App\Enums\UserType;
use App\Enums\ConversationState;

class ConversationManager
{
    public static function execute(array $payload)
    {
        $value = $payload['entry'][0]['changes'][0]['value'] ?? [];

        // Ignore webhook events that are not incoming messages
        if (! isset($value['messages'][0])) {
            return;
        }

        // 1. Extract the message from the webhook
        $message = self::extractMessage($payload);

        \Log::info('WhatsApp Webhook', ['text' => $message['text']]);

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

        // 6. Hand off to the router
        ConversationRouter::execute(
            $conversation,
            $message
        );
    }

    private static function extractMessage(array $payload)
    {
        $value = $payload['entry'][0]['changes'][0]['value'];

        return [
            'phone_number_id' => $value['metadata']['phone_number_id'],
            'from' => $value['messages'][0]['from'],
            'text' => $value['messages'][0]['text']['body'] ?? null,
            'type' => $value['messages'][0]['type'],
            'message_id' => $value['messages'][0]['id'],
        ];
    }
}