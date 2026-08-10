<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\Models\WhatsAppConversation;
use App\Enums\ConversationState;
use App\APIServices\WhatsApp\ExecutionRouter;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookAppointmentTool
{
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'start_booking_or_reschedule_flow',
                'description' => 'Transfer the user to the interactive appointment booking system when they explicitly ask to book, schedule, or reserve an appointment.',
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
                'state' => ConversationState::BOOK_APPOINTMENT,
                'step'  => null,
            ]);

            ExecutionRouter::execute($conversation, $message);

            return [
                'status'  => 'success',
                'message' => 'User transferred to interactive booking flow.',
            ];

        } catch (Throwable $e) {
            Log::error('BookAppointmentTool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args'      => $args,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to initiate booking flow: ' . $e->getMessage(),
            ];
        }
    }
}