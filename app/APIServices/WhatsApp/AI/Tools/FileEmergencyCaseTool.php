<?php

namespace App\APIServices\WhatsApp\AI\Tools;

use App\APIServices\WhatsApp\ExecutionRouter;
use App\Enums\ConversationState;
use Illuminate\Support\Facades\Log;
use Throwable;

class FileEmergencyCaseTool
{
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'file_emergency_case',
                'description' => 'Transfer the patient to the emergency case filing workflow when they report a medical emergency. The workflow will collect the symptoms, whether the patient is at home or in a hospital, and their location or hospital name.',
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
                    'required' => [],
                ],
            ],
        ];
    }

    public static function handle(array $args): array
    {
        try {
            $conversation = $args['conversation'];
            $message = $args['message'];

            if ($conversation->state !== ConversationState::EMERGENCY_CASE) {
                $conversation->update([
                    'state' => ConversationState::EMERGENCY_CASE,
                    'step' => null,
                ]);
            }

            ExecutionRouter::execute(
                $conversation,
                $message
            );

            return [
                'status' => 'success',
                'message' => 'User transferred to emergency case filing workflow.',
            ];

        } catch (Throwable $e) {
            Log::error(
                'FileEmergencyCaseTool Error: ' . $e->getMessage(),
                [
                    'exception' => $e,
                    'args' => $args,
                ]
            );

            return [
                'status' => 'error',
                'message' => 'Failed to initiate emergency case workflow.',
            ];
        }
    }
}