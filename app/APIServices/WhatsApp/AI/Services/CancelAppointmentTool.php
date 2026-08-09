<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\Models\WhatsAppConversation;
use App\Enums\ConversationState;
use App\APIServices\WhatsApp\ExecutionRouter;
use Illuminate\Support\Facades\Log;
use Throwable;

class CancelAppointmentTool
{
    /**
     * Define the schema so the AI model knows when and how to call this tool.
     */
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'start_cancellation_flow',
                'description' => 'Transfer the user to the interactive appointment cancellation flow when they explicitly ask to cancel, revoke, or drop an appointment.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'patient_id' => [
                            'type' => 'integer',
                            'description' => 'The integer ID of the patient.',
                        ],
                    ],
                    'required' => ['patient_id'],
                ],
            ],
        ];
    }

    /**
     * Execute resetting the conversation state and routing to the cancellation flow.
     */
    public static function handle(array $args): array
    {
        Log::info('CancelAppointmentTool Called', ['args' => $args]);

        try {
            $patientId = $args['patient_id'] ?? null;

            if (!$patientId) {
                return [
                    'status' => 'error',
                    'message' => 'patient_id is required.',
                ];
            }

            // 1. Fetch the active conversation for this patient
            $conversation = WhatsAppConversation::where('patient_id', $patientId)
                ->latest('last_activity_at')
                ->first();

            if (!$conversation) {
                return [
                    'status' => 'error',
                    'message' => "No active conversation found for patient ID {$patientId}.",
                ];
            }

            // 2. Update conversation state to CANCEL_APPOINTMENT
            $conversation->update([
                'state' => ConversationState::CANCEL_APPOINTMENT,
                'step'  => null,
            ]);

            // 3. Create a fake message payload mimicking an interactive cancellation trigger
            $fakeMessage = [
                'phone_number_id' => $conversation->doctorWhatsAppAccount->phone_number_id ?? null,
                'from'            => $conversation->phone_number,
                'type'            => 'text',
                'value'           => 'CANCEL_APPOINTMENT',
                'message_id'      => 'fake_ai_cancel_' . uniqid(),
            ];

            // 4. Route execution back to the main router
            return ExecutionRouter::execute($conversation, $fakeMessage);

        } catch (Throwable $e) {
            Log::error('CancelAppointmentTool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args'      => $args,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to initiate appointment cancellation flow: ' . $e->getMessage(),
            ];
        }
    }
}