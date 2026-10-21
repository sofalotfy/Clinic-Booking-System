<?php

namespace App\Services\Patients\Retrievals;

use App\Models\User;
use App\Models\Patient;
use App\Services\Appointments\Retrievals\ListAppointments;

class ListDoctorPatients
{
    public static function execute(User $user, $filters = null)
    {
        $doctorId = $user->clinicDoctorId();

        $patients = Patient::leftJoin('appointments', 'appointments.patient_id', '=', 'patients.id')
                        ->leftJoin('users', 'patients.user_id', '=', 'users.id')
                        ->where('appointments.doctor_id', $doctorId)
                        ->when($filters, fn ($builder) => self::filter($builder, $filters, $doctorId))
                        ->groupBy('patients.id');

        return $patients;
    }

    private static function filter($builder, $filters, int $doctorId)
    {
        return $builder
            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '>=', $filters['date_from']);
            })

            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('appointments.date', '<=', $filters['date_to']);
            })

            ->when(isset($filters['age_from']), function ($query) use ($filters) {
                $query->where('users.age', '>=', $filters['age_from']);
            })

            ->when(isset($filters['age_to']), function ($query) use ($filters) {
                $query->where('users.age', '<=', $filters['age_to']);
            })

            ->when(
                isset($filters['has_upcoming_appointment'])
                    && $filters['has_upcoming_appointment'],
                function ($query) {
                    $query->havingRaw(
                        'MIN(CASE WHEN appointments.date >= NOW() THEN appointments.date END) IS NOT NULL'
                    );
                }
            )

            ->when(isset($filters['search']), function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->where('users.name', 'like', "%{$filters['search']}%")
                        ->orWhere('users.phone', 'like', "%{$filters['search']}%")
                        ->orWhere('users.area', 'like', "%{$filters['search']}%");
                });
            })

            ->when(isset($filters['gender']), function ($query) use ($filters) {
                $query->where('users.gender', $filters['gender']);
            })

            ->when(isset($filters['is_blocked']), function ($query) use ($filters, $doctorId) {
                $blocked = fn ($blocks) => $blocks->where('doctor_id', $doctorId)->active();

                $filters['is_blocked']
                    ? $query->whereHas('blocks', $blocked)
                    : $query->whereDoesntHave('blocks', $blocked);
            })

            ->when(isset($filters['no_flags']) && $filters['no_flags'], function ($query) use ($doctorId) {
                $query->whereDoesntHave('flags', fn ($flags) => $flags->where('flag_patient.doctor_id', $doctorId));
            })

            ->when(! empty($filters['flags']), function ($query) use ($filters, $doctorId) {
                self::filterByFlags($query, $filters['flags'], ($filters['flag_match'] ?? 'any') === 'all', $doctorId);
            });
    }

    /**
     * any: the patient carries at least one of the flags.
     * all: the patient carries every one of them.
     * Only the flags this clinic put on the patient count.
     */
    private static function filterByFlags($query, array $flagIds, bool $matchAll, int $doctorId): void
    {
        $withFlag = fn (array $ids) => fn ($flags) => $flags
            ->where('flag_patient.doctor_id', $doctorId)
            ->whereIn('flags.id', $ids);

        if (! $matchAll) {
            $query->whereHas('flags', $withFlag($flagIds));

            return;
        }

        foreach ($flagIds as $flagId) {
            $query->whereHas('flags', $withFlag([$flagId]));
        }
    }
}