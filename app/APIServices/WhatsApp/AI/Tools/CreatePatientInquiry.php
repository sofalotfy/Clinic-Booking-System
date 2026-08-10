<?php

namespace App\APIServices\WhatsApp\AI\Tools;

use App\Models\WhatsAppConversation;
use Illuminate\Support\Facades\Log;
use App\Services\PatientInquiry\CreatePatientInquiry;
use Throwable;

class CreatePatientInquiryTool
{
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'create_patient_inquiry',
                'description' => 'Create a new inquiry for a patient when they ask a question that cannot be answered by the AI assistant.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [],
                ],
            ],
        ];
    }

    public static function handle(array $args): array
    {
        try {
            $conversation = $args['conversation'];
            $message = $args['message'];

            CreatePatientInquiry::execute(
                patientId: $conversation->patient_id,
                doctorId: $conversation->doctor_id,
                question: $message->content,
            );

            return [
                'status'  => 'success',
                'message' => 'Your inquiry has been successfully received and forwarded to the appropriate department for review.',
            ];

        } catch (Throwable $e) {
            Log::error('CreatePatientInquiryTool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args'      => $args,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to create patient inquiry: ' . $e->getMessage(),
            ];
        }
    }
}