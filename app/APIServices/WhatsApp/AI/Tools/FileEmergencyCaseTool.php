<?php

namespace App\APIServices\WhatsApp\AI\Tools;

use App\Models\EmergencyCase;
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
                'description' => 'Create or update the patient emergency case. Use this tool whenever the patient reports a medical emergency. Information can be provided incrementally. Only provide information explicitly given by the patient; never invent missing values.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [

                        'context' => [
                            'type' => 'string',
                            'description' => 'Description of the emergency situation or what happened. Only include information provided by the patient.',
                        ],

                        'symptoms' => [
                            'type' => 'string',
                            'description' => 'The symptoms reported by the patient. Only include symptoms explicitly mentioned by the patient.',
                        ],

                        'latitude' => [
                            'type' => 'number',
                            'description' => 'Latitude of the patient location, if provided.',
                        ],

                        'longitude' => [
                            'type' => 'number',
                            'description' => 'Longitude of the patient location, if provided.',
                        ],

                        'place' => [
                            'type' => 'string',
                            'description' => 'Name of the place or location, if provided by the patient.',
                        ],

                        'address' => [
                            'type' => 'string',
                            'description' => 'Address of the patient location, if provided.',
                        ],

                        'in_hospital' => [
                            'type' => 'boolean',
                            'description' => 'Whether the patient is currently in a hospital.',
                        ],

                        'hospital_name' => [
                            'type' => 'string',
                            'description' => 'Name of the hospital if the patient is currently in a hospital.',
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
            $patientId = $args['patient_id'] ?? null;
            $doctorId = $args['doctor_id'] ?? null;

            if (!$patientId) {
                return [
                    'status' => 'error',
                    'message' => 'Patient ID is required.',
                ];
            }

            $emergencyCase = EmergencyCase::updateOrCreate(
                [
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                ],
                collect($args)
                    ->except([
                        'patient_id',
                        'doctor_id',
                        'conversation',
                        'message',
                    ])
                    ->filter(fn ($value) => $value !== null)
                    ->toArray()
            );

            return [
                'status' => 'success',
                'message' => 'Emergency case filed successfully.',
                'emergency_case_id' => $emergencyCase->id,
            ];

        } catch (Throwable $e) {
            Log::error('FileEmergencyCaseTool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args' => $args,
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to file emergency case.',
            ];
        }
    }
}