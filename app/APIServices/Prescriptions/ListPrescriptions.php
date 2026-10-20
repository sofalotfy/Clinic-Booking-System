<?php

namespace App\APIServices\Prescriptions;

use App\Services\Prescriptions\ListPrescriptions as ListService;

class ListPrescriptions
{
    public static function execute($request)
    {
        $filters = $request->validate([
            'patient_id' => 'nullable|integer',
            'appointment_id' => 'nullable|integer',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
            'has_consultation' => 'nullable|boolean',
            'consultation_from' => 'nullable|date_format:Y-m-d',
            'consultation_to' => 'nullable|date_format:Y-m-d',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return ListService::execute($request->user(), $filters)
            ->with(['appointment', 'patient.user', 'medicines', 'labs', 'scans'])
            ->paginate($filters['per_page'] ?? 20)
            ->through(fn ($prescription) => FormatPrescription::execute($prescription));
    }
}
