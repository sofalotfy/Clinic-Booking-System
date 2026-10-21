<?php

namespace App\APIServices\Appointments;

use App\Enums\AppointmentGrade;
use App\Enums\AppointmentStatus;
use App\Enums\Gender;
use App\Services\Appointments\Retrievals\ListAppointments as ListService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ListAppointments
{
    public static function execute($request)
    {
       return ListService::execute($request->user(), self::filters($request))
        ->select(self::getSelects())
        ->get()
        ->transform(function ($appointment){
            $appointment->avatar = $appointment->avatar
                ? asset('storage/' . $appointment->avatar)
                : null;

            return $appointment;
        });
    }

    /**
     * status and grade take one value, a comma separated list or an array:
     * ?status=Active, ?status=Active,Pending or ?status[]=Active&status[]=Pending
     */
    private static function filters($request): array
    {
        foreach (['status', 'grade'] as $key) {
            if (is_string($request->input($key))) {
                $request->merge([$key => array_filter(array_map('trim', explode(',', $request->input($key))))]);
            }
        }

        return $request->validate([
            'patient_id' => 'nullable|integer',
            'date' => 'nullable|date_format:Y-m-d',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'status' => 'nullable|array',
            'status.*' => [Rule::enum(AppointmentStatus::class)],
            'grade' => 'nullable|array',
            'grade.*' => [Rule::enum(AppointmentGrade::class)],
            'is_confirmed' => 'nullable|boolean',
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'search' => 'nullable|string|max:255',
            'has_prescription' => 'nullable|boolean',
            'sort' => 'nullable|in:asc,desc',
        ]);
    }

    private static function getSelects()
    {
        return [
            "appointments.id",
            "appointments.patient_id",
            "appointments.duration",
            "appointments.status",
            DB::raw('DATE(DATE_ADD(appointments.date, INTERVAL COALESCE(appointments.delay, 0) MINUTE)) as date'),
            DB::raw('TIME(DATE_ADD(appointments.date, INTERVAL COALESCE(appointments.delay, 0) MINUTE)) as time'),
            "appointments.type",
            "appointments.grade",
            "users.name as patient_name",
            "users.phone as patient_phone",
            "users.image as avatar",
            DB::raw('(users.age + TIMESTAMPDIFF(YEAR, users.created_at, CURDATE())) as patient_age'),
            "users.gender as patient_gender",
            "users.area as patient_area",
        ];
    }
}