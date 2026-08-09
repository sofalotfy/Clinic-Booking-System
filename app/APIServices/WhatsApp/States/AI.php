<?php

namespace App\APIServices\WhatsApp\States;

use App\Models\WhatsAppConversation;
use App\Models\WhatsappMessages;
use App\APIServices\WhatsApp\SendMessage;
use App\APIServices\WhatsApp\AI\WhatsAppAiService;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\GetUpComingAppointment;

class AI
{
    /**
     * Transition the conversation state into AI mode and trigger initial execution.
     */
    public static function execute(WhatsAppConversation $conversation, array $message): void
    {
        $conversation->update([
            'state' => \App\Enums\ConversationState::AI,
        ]);

        self::handleResponse($conversation, $message);
    }

    /**
     * Handle incoming patient response while in AI state.
     */
    public static function handleResponse(WhatsAppConversation $conversation, array $message): void
    {
        if ($message['type'] !== 'text' || empty($message['value'])) {
            return;
        }

        try {
            $userText = $message['value'];
            $doctorAccount = DoctorWhatsAppAccount::findOrFail(
                $conversation->doctor_whatsapp_account_id
            );
            $patient = $conversation->patient;

            // 1. Fetch history BEFORE appending current message
            $history = WhatsappMessages::getHistory($conversation->id, 5);

            // 2. Save incoming user message
            $conversation->messages()->create([
                'role'    => 'user',
                'content' => $userText,
            ]);

            $appointment = GetUpComingAppointment::execute($patient->id, $doctorAccount->doctor_id);

            // 3. Build dynamic context payload
            $context = [
                'doctor_id'     => (int) ($doctorAccount->doctor_id ?? $doctorAccount->id),
                'doctor_name'   => $doctorAccount->doctor->user->name ?? 'Specialist',
                'patient_id'    => $patient->id ?? null,
                'patient_name'  => $patient->user->name ?? 'Patient',
                'patient_phone' => $message['from'],
                'appointment_date' => $appointment->date ?? null,
            ];

            // 4. Execute AI pipeline
            $aiService = app(WhatsAppAiService::class);
            $replyText = $aiService->ask($userText, $history, $context);

            // 5. Save assistant reply to history
            $conversation->messages()->create([
                'role'    => 'assistant',
                'content' => $replyText,
            ]);

            // 6. Dispatch message to patient via WhatsApp API
            SendMessage::text(
                $doctorAccount->phone_number_id,
                $doctorAccount->access_token,
                $message['from'],
                $replyText
            );

        } catch (Throwable $e) {
            Log::error('WhatsApp AI State Processing Error: ' . $e->getMessage(), [
                'exception'       => $e,
                'conversation_id' => $conversation->id,
            ]);
        }
    }
}