<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\Models\WhatsAppConversation;
use App\Enums\ConversationState;
use App\APIServices\WhatsApp\ExecutionRouter;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\Appointments\GetUpComingAppointment;

class CancelAppointmentTool
{
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
                        'doctor_id' => [
                            'type' => 'integer',
                            'description' => 'The integer ID of the assigned doctor.',
                        ],
                    ],
                    'required' => ['patient_id'],
                ],
            ],
        ];
    }

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
            
            $conversation = WhatsAppConversation::where('patient_id', $patientId)
                ->latest('last_activity_at')
                ->first();

            if (!$conversation) {
                return [
                    'status' => 'error',
                    'message' => "No active conversation found for patient ID {$patientId}.",
                ];
            }

            $appointment = GetUpComingAppointment::execute($patientId, $conversation->doctor_id);

            if (!$appointment) {
                return [
                    'status' => 'error',
                    'message' => "You don't have an active appointment to cancel.",
                ];
            }

            $conversation->update([
                'state' => ConversationState::CANCEL_APPOINTMENT,
                'step'  => null,
            ]);

            $fakeMessage = [
                'phone_number_id' => $conversation->doctorWhatsAppAccount->phone_number_id ?? null,
                'from'            => $conversation->phone_number,
                'type'            => 'text',
                'value'           => 'CANCEL_APPOINTMENT',
                'message_id'      => 'fake_ai_cancel_' . uniqid(),
            ];

            ExecutionRouter::execute($conversation, $fakeMessage);

            return [
                'status'  => 'success',
                'message' => 'User transferred to interactive cancellation flow.',
            ];

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