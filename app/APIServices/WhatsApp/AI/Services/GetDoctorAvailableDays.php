<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\APIServices\Doctors\GetAvailableDays;
use Illuminate\Support\Facades\DB;

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
                'name' => 'get_doctor_available_days',
                'description' => 'Retrieve available booking days for a doctor on a specific date.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'doctor_id' => [
                            'type' => 'integer',
                            'description' => 'The ID of the doctor. it\'s always 1',
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
        \Log::info('GetDoctorAvailableDaysTool called with:', $args);
        
        $doctorId = $args['doctor_id'];
        
        $days = GetAvailableDays::execute($doctorId);

        return ['status' => 'success', 'days' => $days];
    }
}