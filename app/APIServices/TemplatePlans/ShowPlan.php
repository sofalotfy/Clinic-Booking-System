<?php

namespace App\APIServices\TemplatePlans;

use App\Services\TemplatePlans\Retrievals\ShowPlan as ShowService;

class ShowPlan
{
    public static function execute($request, $templatePlan)
    {
        $plan = ShowService::execute($request->user(), $templatePlan)
            ->select(self::getSelects())
            ->get();

        return response()->json([
            'plan' => self::format($plan),
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

            'template_days.id as template_day_id',
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
            $first = $plan->first();

            return [
                'id' => $first->id,
                'name' => $first->name,
                'description' => $first->description,
                'status' => $first->status,
                'created_at' => $first->created_at,
                'updated_at' => $first->updated_at,

                'template_days' => $plan
                    ->whereNotNull('template_day_id')
                    ->map(fn ($day) => [
                        'id' => $day->template_day_id,
                        'day_of_week' => $day->day_of_week,
                        'start_time' => $day->start_time,
                        'end_time' => $day->end_time,
                        'appointment_duration' => $day->appointment_duration,
                        'queue_length' => $day->queue_length,
                    ])
                    ->values(),
            ];
        })->first();
    }
}