<?php

namespace App\APIServices\WhatsApp\AI\Tools;

use App\Models\WhatsAppConversation;
use Illuminate\Support\Facades\Log;
use App\Services\PatientInquiries\CreatePatientInquiry;
use Throwable;
use App\Models\DoctorWhatsAppAccount;

class CreatePatientInquiryTool
{
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'ask_doctor',
                'description' => 'Create a new inquiry for a patient when they ask a question that cannot be answered by the AI assistant.',
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
                        'inquiry' => [
                            'type' => 'string',
                            'description' => 'the question the user wants to ask.',
                        ],
                    ],
                    'required' => ['inquiry'],
                ],
            ],
        ];
    }

    public static function handle(array $args): array
    {
        \Log::info(
            "tool called: ask doctor"
        );
        try {
            $conversation = $args['conversation'];
            $message = $args['message'];
            $account = DoctorWhatsAppAccount::findOrFail(
                        $conversation->doctor_whatsapp_account_id
                    );
            CreatePatientInquiry::execute(
                patientId: $conversation->patient_id,
                doctorId: $args['doctor_id'],
                question: $args['inquiry'],
            );

            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'Your inquiry has been successfully received and forwarded to the appropriate department for review.',
            );

            return [
                'status'  => 'success',
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