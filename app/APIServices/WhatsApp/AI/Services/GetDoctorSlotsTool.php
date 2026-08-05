<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\APIServices\Days\GetAvailableSlots;
use App\Models\Day;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetDoctorSlotsTool
{
    /**
     * Define the schema so the AI model knows when and how to call this tool.
     */
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'get_available_slots',
                'description' => 'Retrieve available booking time slots for a doctor on a specific date.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'doctor_id' => [
                            'type' => 'integer',
                            'description' => 'The integer ID of the doctor.',
                        ],
                        'date' => [
                            'type' => 'string',
                            'description' => 'The target date in YYYY-MM-DD format (e.g., "2026-08-06").',
                        ],
                    ],
                    'required' => ['doctor_id', 'date'],
                ],
            ],
        ];
    }

    /**
     * Execute the query against your database/service.
     */
    public static function handle(array $args): array
    {
        Log::info('GetDoctorSlotsTool Called', ['args' => $args]);

        try {
            $doctorId = $args['doctor_id'] ?? null;
            $date = $args['date'] ?? null;

            if (!$doctorId || !$date) {
                return [
                    'status' => 'error',
                    'message' => 'Both doctor_id and date are required parameters.',
                ];
            }

            // Find the day record for the given doctor and date
            $day = Day::where('doctor_id', $doctorId)
                ->where('date', $date)
                ->first();

            if (!$day) {
                return [
                    'status' => 'no_schedule',
                    'message' => "No working schedule record found for doctor ID {$doctorId} on {$date}.",
                ];
            }

            // Execute service using the resolved day ID
            $slots = GetAvailableSlots::execute($day->id);

            if (empty($slots)) {
                return [
                    'status' => 'no_slots',
                    'message' => "No available slots found for doctor ID {$doctorId} on {$date}.",
                ];
            }

            return [
                'status' => 'success',
                'doctor_id' => (int) $doctorId,
                'date' => $date,
                'slots' => $slots,
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