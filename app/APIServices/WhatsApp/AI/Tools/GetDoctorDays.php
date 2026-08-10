<?php

namespace App\APIServices\WhatsApp\AI\Tools;

use App\APIServices\Days\GetDays;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetDoctorDays
{
    /**
     * Define the schema so the AI model knows when and how to call this tool.
     */
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'get_days_ids',
                'description' => 'Retrieve available working schedule days for a doctor using the doctor ID.',
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
     * Execute the query against your database/service.
     */
    public static function handle(array $args): array
    {
        Log::info('GetDoctorDays Tool Called', ['args' => $args]);

        try {
            $doctorId = $args['doctor_id'] ?? null;

            if (!$doctorId) {
                return [
                    'status' => 'error',
                    'message' => 'Doctor ID is required.',
                ];
            }

            $doctor = Doctor::find($doctorId);

            if (!$doctor) {
                return [
                    'status' => 'error',
                    'message' => "Doctor with ID {$doctorId} was not found.",
                ];
            }

            $days = GetDays::execute(null, $doctor);

            if (empty($days)) {
                return [
                    'status' => 'no_days_found',
                    'message' => 'No available booking days found for this doctor.',
                ];
            }

            return [
                'status' => 'success',
                'doctor_id' => (int) $doctorId,
                'days' => $days,
            ];

        } catch (Throwable $e) {
            Log::error('GetDoctorDays Tool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args' => $args,
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to retrieve working days: ' . $e->getMessage(),
            ];
        }
    }
}