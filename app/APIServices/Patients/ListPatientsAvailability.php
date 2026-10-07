<?php

namespace App\APIServices\Patients;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\Patients\Retrievals\ListPatientsAvailability as ListPatientsAvailabilityService;

class ListPatientsAvailability
{
    public static function execute($request)
    {
        $user = $request->user();
        $doctor = $user->clinicDoctor();

        return ListPatientsAvailabilityService::execute($user)
            ->select(
                'patients.id as id',
                'users.name as name',
                'users.phone as phone',
                'users.email as email',
                'users.image as avatar',
                'users.age as age',
                'users.area as area',
            )
            ->addSelect([
                'has_appointment' => Appointment::query()
                    ->selectRaw('1')
                    ->whereColumn('appointments.patient_id', 'patients.id')
                    ->where('appointments.doctor_id', $doctor->id)
                    ->where('date', '>=', now())
                    ->whereIn('appointments.status', AppointmentStatus::working())
                    ->limit(1),
            ])
            ->orderBy('users.name')
            ->get()
            ->map(function ($patient) {
                $patient->avatar = $patient->avatar
                    ? asset('storage/'.$patient->avatar)
                    : null;

                $patient->has_appointment = (bool) $patient->has_appointment;

                return $patient;
            });
    }
}
