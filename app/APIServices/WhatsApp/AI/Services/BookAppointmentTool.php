<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\Models\WhatsAppConversation;
use App\Enums\ConversationState;
use App\APIServices\WhatsApp\ExecutionRouter;
use App\APIServices\Appointments\SmartBookAppointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookAppointmentTool
{
    /**
     * Define the schema so the AI model knows when and how to call this tool.
     */
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'start_booking_or_reschedule_flow', // Changed name from book_appointment to avoid confusion
                'description' => 'Transfer the user to the interactive appointment booking system when they explicitly ask to book, schedule, or reserve an appointment.',
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
     * Execute the appointment booking/updating logic.
     */
    public static function handle(array $args): array
    {
        Log::info('BookAppointmentTool Called', ['args' => $args]);

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

            // 2. Update conversation state to MAIN_MENU
            $conversation->update([
                'state' => ConversationState::BOOK_APPOINTMENT,
                'step'  => null,
            ]);

            // 3. Create a fake message payload mimicking an interactive reset
            $fakeMessage = [
                'phone_number_id' => $conversation->doctorWhatsAppAccount->phone_number_id ?? null,
                'from'            => $conversation->phone_number,
                'type'            => 'text',
                'value'           => 'BOOK_APPOINTMENT',
                'message_id'      => 'fake_ai_exit_' . uniqid(),
            ];

            // 4. Route execution back to the main router
            return ExecutionRouter::execute($conversation, $fakeMessage);

        } catch (Throwable $e) {
            Log::error('ExitAiModeTool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args'      => $args,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to exit AI mode: ' . $e->getMessage(),
            ];
        }
    }
}