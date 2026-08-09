<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\APIServices\Days\GetDays;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\APIServices\WhatsApp\ExecutionRouter;

class GuideTemplate
{
    /**
     * Define the schema so the AI model knows when and how to call this tool.
     */
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'send to the template chat',
                'description' => 'exit ai mode.',
                'parameters' => [],
            ],
        ];
    }

    /**
     * Execute the query against your database/service.
     */
    public static function handle(array $args): array
    {
        Log::info('exit ai mode', ['args' => $args]);

        try {
            
            ExecutionRouter::execute()

            return [
                'status' => 'success',
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