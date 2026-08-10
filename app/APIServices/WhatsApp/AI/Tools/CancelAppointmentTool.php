<?php

namespace App\APIServices\WhatsApp\AI\Tools;

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
        try {
            $conversation = $args['conversation'];
            $message = $args['message'];

            $conversation->update([
                'state' => ConversationState::CANCEL_APPOINTMENT,
                'step'  => null,
            ]);

            ExecutionRouter::execute($conversation, $message);

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