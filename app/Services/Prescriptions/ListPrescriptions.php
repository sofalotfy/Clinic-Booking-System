<?php

namespace App\Services\Prescriptions;

use App\Models\Prescription;

class ListPrescriptions
{
    public static function execute($user, array $filters = [])
    {
        return Prescription::query()
            ->where('doctor_id', $user->clinicDoctorId())
            ->when(isset($filters['patient_id']), fn ($query) => $query->where('patient_id', $filters['patient_id']))
            ->when(isset($filters['appointment_id']), fn ($query) => $query->where('appointment_id', $filters['appointment_id']))
            ->when(isset($filters['from']), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']))
            ->when(isset($filters['has_consultation']), fn ($query) => $filters['has_consultation']
                ? $query->whereNotNull('consultation_date')
                : $query->whereNull('consultation_date'))
            ->when(isset($filters['consultation_from']), fn ($query) => $query->whereDate('consultation_date', '>=', $filters['consultation_from']))
            ->when(isset($filters['consultation_to']), fn ($query) => $query->whereDate('consultation_date', '<=', $filters['consultation_to']))
            ->latest();
    }
}
