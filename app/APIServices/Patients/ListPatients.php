<?php

namespace App\APIServices\Patients;

use App\Enums\Gender;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\Patients\Retrievals\ListDoctorPatients;

class ListPatients
{
    public static function execute($request)
    {
        $filters = [
            'age_from' => $request->input('age_from'),
            'age_to' => $request->input('age_to'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'has_upcoming_appointment' => $request->input('has_upcoming_appointment'),
            'search' => $request->input('search'),
            ...self::newFilters($request),
        ];
        $user = $request->user();
        $doctor = $user->clinicDoctor();

        $patients = ListDoctorPatients::execute($user, $filters)
            ->select(self::getSelects())
            ->paginate($request->input('per_page', 15));

        $patients->getCollection()->load([
            'flags' => function ($query) use ($doctor) {
                $query->wherePivot('doctor_id', $doctor->id);
            },

            'notes' => function ($query) use ($doctor) {
                $query->where('notes.doctor_id', $doctor->id);
            },
            
            'blocks' => function ($query) use ($doctor) {
                $query
                    ->where('doctor_id', $doctor->id)
                    ->active();
            },
        ]);

        $patients->getCollection()->transform(function ($patient) {
            $patient->avatar = $patient->avatar
                ? asset('storage/' . $patient->avatar)
                : null;

            $patient->flags = $patient->flags->values();

            $patient->block = $patient->blocks->first();
            $patient->is_blocked = $patient->block !== null;

            unset($patient->blocks);

            return $patient;
        });

        return $patients;
    }

    /**
     * flags takes ids as a comma separated list or an array, and only this
     * clinic's own flags are accepted:
     * ?flags=3,5 (any of them), ?flags=3,5&flag_match=all, ?no_flags=1
     */
    private static function newFilters($request): array
    {
        if (is_string($request->input('flags'))) {
            $request->merge(['flags' => array_filter(array_map('trim', explode(',', $request->input('flags'))))]);
        }

        return $request->validate([
            'flags' => 'nullable|array',
            'flags.*' => ['integer', Rule::exists('flags', 'id')->where('doctor_id', $request->user()->clinicDoctorId())],
            'flag_match' => 'nullable|in:any,all',
            'no_flags' => 'nullable|boolean',
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'is_blocked' => 'nullable|boolean',
        ]);
    }

    private static function getSelects()
    {
        return [
            'patients.id as id',
            'users.name as name',
            'users.phone as phone',
            'users.email as email',
            'users.image as avatar',
            'users.age as age',
            'users.area as area',

            DB::raw(
                'DATE(MAX(CASE WHEN appointments.date < NOW() THEN appointments.date END)) as last_appointment_date'
            ),

            DB::raw(
                'TIME(MAX(CASE WHEN appointments.date < NOW() THEN appointments.date END)) as last_appointment_time'
            ),

            DB::raw(
                'DATE(MIN(CASE WHEN appointments.date >= NOW() THEN appointments.date END)) as upcoming_appointment_date'
            ),

            DB::raw(
                'TIME(MIN(CASE WHEN appointments.date >= NOW() THEN appointments.date END)) as upcoming_appointment_time'
            ),
        ];
    }
}