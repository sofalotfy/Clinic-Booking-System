<?php

namespace App\APIServices\Patients;

use Illuminate\Support\Facades\DB;
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