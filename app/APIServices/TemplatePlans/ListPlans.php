<?php

namespace App\APIServices\TemplatePlans;

use App\Services\TemplatePlans\Retrievals\ListPlans as ListPlansService;

class ListPlans
{
    public static function execute($request)
    {
        $plans = ListPlansService::execute($request->user())
            ->select(self::getSelects())
            ->get();

        return response()->json([
            "plans" => self::format($plans),
        ]);
    }

    private static function getSelects()
    {
        return [
            'template_plans.id',
            'template_plans.name',
            'template_plans.description',
            'template_plans.status',
            'template_plans.created_at',
            'template_plans.updated_at',

            'template_days.id as day_id',
            'template_days.day_of_week',
            'template_days.start_time',
            'template_days.end_time',
            'template_days.appointment_duration',
            'template_days.queue_length',
        ];
    }

    private static function format($plans)
    {
        return $plans->groupBy('id')->map(function ($plan) {
            return [
                'id' => $plan->first()->id,
                'name' => $plan->first()->name,
                'description' => $plan->first()->description,
                'status' => $plan->first()->status,
                'created_at' => $plan->first()->created_at,
                'updated_at' => $plan->first()->updated_at,
                'template_days' => $plan
                    ->whereNotNull('day_id')
                    ->unique('day_id')
                    ->map(fn ($day) => [
                        'id' => $day->day_id,
                        'day_of_week' => $day->day_of_week,
                        'start_time' => $day->start_time,
                        'end_time' => $day->end_time,
                        'appointment_duration' => $day->appointment_duration,
                        'queue_length' => $day->queue_length,
                    ])
                    ->values(),
            ];
        })->values();
    }
}