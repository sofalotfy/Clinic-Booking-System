<?php

namespace App\APIServices\WhatsApp\AI\Tools;

use App\APIServices\Doctors\GetAvailableDays;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetDoctorAvailableDays
{
    /**
     * Define the schema so the AI model knows when and how to call this tool.
     */
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'get_available_days',
                'description' => 'Retrieve summary of available booking dates for a doctor.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'doctor_id' => [
                            'type' => 'integer',
                            'description' => 'The integer ID of the doctor.',
                        ],
                    ],
                    'required' => ['doctor_id'],
                ],
            ],
        ];
    }

    /**
     * Execute the query against your database or AI view.
     */
    public static function handle(array $args): array
    {
        Log::info('GetDoctorAvailableDays Tool Called', ['args' => $args]);

        try {
            $doctorId = $args['doctor_id'] ?? null;

            if (!$doctorId) {
                return [
                    'status' => 'error',
                    'message' => 'Doctor ID is required.',
                ];
            }

            $days = GetAvailableDays::execute($doctorId);

            if (empty($days)) {
                return [
                    'status' => 'no_available_days',
                    'message' => 'No available days found for doctor ID ' . $doctorId,
                ];
            }

            return [
                'status' => 'success',
                'doctor_id' => (int) $doctorId,
                'days' => $days,
            ];

        } catch (Throwable $e) {
            Log::error('GetDoctorAvailableDays Tool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args' => $args,
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to retrieve available days: ' . $e->getMessage(),
            ];
        }
    }
}