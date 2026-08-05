<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\APIServices\Days\GetAvailableSlots;
use Illuminate\Support\Facades\DB;

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
                'name' => 'get_doctor_available_slots',
                'description' => 'Retrieve available booking slots for a doctor on a specific date.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'day_id' => [
                            'type' => 'integer',
                            'description' => 'The integer ID of the day.',
                        ],
                    ],
                    'required' => ['day_id'],
                ],
            ],
        ];
    }

    /**
     * Execute the query against your database or AI view.
     */
    public static function handle(array $args): array
    {
        \Log::info('GetDoctorSlotsTool called with:', $args);
        $dayId = $args['day_id'];
        $day = Day::find($dayId);
        if(!$day){
            return ['status' => 'error', 'message' => 'Day not found.'];
        }
        $slots = GetAvailableSlots::execute($dayId);

        if (empty($slots)) {
            return ['status' => 'no_slots', 'message' => 'No available slots found for this date.'];
        }

        return ['status' => 'success', 'slots' => $slots];

        // Query available slots (adjust table/view name to match your database)
        $slots = DB::table('doctor_schedules')
            ->where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where('is_booked', false)
            ->select('id', 'start_time', 'end_time')
            ->get();

        if ($slots->isEmpty()) {
            return ['status' => 'no_slots', 'message' => 'No available slots found for this date.'];
        }

        return ['status' => 'success', 'slots' => $slots->toArray()];
    }
}