<?php

namespace App\APIServices\WhatsApp\AI\Tools;

use App\APIServices\Days\GetAvailableSlots;
use App\Models\Day;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\Assistants\GetAssistantsContacts;
use App\APIServices\WhatsApp\Services\ListAssistantsContacts;
use App\Models\Doctor;

class ListAssistants
{
    /**
     * Define the schema so the AI model knows when and how to call this tool.
     */
    public static function definition(): array
    {
        return [
        'type' => 'function',
        'function' => [
            'name' => 'list_assistants_contacts',
            'description' => 'Retrieve the list of assistants and their contact information for a specific doctor.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'doctor_id' => [
                        'type' => 'integer',
                        'description' => 'The integer ID of the doctor whose assistants should be listed.',
                    ],
                ],
                'required' => ['doctor_id'],
                'additionalProperties' => false,
            ],
        ],
    ];

    }

    /**
     * Execute the query against your database/service.
     */
    public static function handle(array $args): array
    {
        \Log::info([
                'text' => "tool called: list assistants",
            ]);
        try {
            
            $doctor = Doctor::find($args['doctor_id']);
            $list = GetAssistantsContacts::execute($doctor);
            ListAssistantsContacts::execute($args['conversation'],$args['message'],$list);

            return [
                'status' => 'success',
            ];

        } catch (Throwable $e) {
            Log::error('GetDoctorSlotsTool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args' => $args,
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to retrieve slots: ' . $e->getMessage(),
            ];
        }
    }
}