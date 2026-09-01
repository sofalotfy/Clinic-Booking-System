<?php

namespace App\Services\DaysInstances\Retrievals;

use App\Models\Day;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ListDays
{
    public static function execute(User $user, $filters = null)
    {
        return Day::leftJoin('appointments',function($join) use ($user){
                        $join->on(DB::raw('DATE(appointments.date)'), '=', 'days.date')
                            ->where('appointments.doctor_id', $user->clinicDoctor()->id);
                    })
                    ->where('days.doctor_id', $user->clinicDoctor()->id)
                    ->when($filters, fn ($builder) => self::filter($builder, $filters))
                    ->groupBy('days.id')
                    ->orderBy('days.date', 'desc');
    }

    private static function filter($builder, $filters)
    {
        return $builder
            ->when(isset($filters['status']) && is_array($filters['status']), function ($query) use ($filters) {
                $query->whereIn('appointments.status', $filters['status']);
            })
            ->when(isset($filters['date_from']) && isset($filters['date_to']), function ($query) use ($filters) {
                $query->whereBetween('days.date', [$filters['date_from'], $filters['date_to']]);
            });
    }
}