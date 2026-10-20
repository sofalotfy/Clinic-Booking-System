<?php

namespace App\APIServices\Prescriptions;

use App\Models\Prescription;

class FormatPrescription
{
    public static function execute(Prescription $prescription): array
    {
        return [
            'id' => $prescription->id,
            'appointment_id' => $prescription->appointment_id,
            'appointment_date' => $prescription->appointment?->date,
            'patient' => [
                'id' => $prescription->patient_id,
                'name' => $prescription->patient?->user?->name,
                'phone' => $prescription->patient?->user?->phone,
            ],
            'diagnosis' => $prescription->diagnosis,
            'notes' => $prescription->notes,
            'consultation_date' => $prescription->consultation_date?->toDateString(),
            'medicines' => $prescription->medicines->map(fn ($medicine) => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'name_ar' => $medicine->name_ar,
                'active_ingredient' => $medicine->active_ingredient,
                'strength' => $medicine->strength,
                'dosage_form' => $medicine->dosage_form,
                'dose' => $medicine->pivot->dose,
                'frequency' => $medicine->pivot->frequency,
                'duration' => $medicine->pivot->duration,
                'instructions' => $medicine->pivot->instructions,
            ])->values(),
            'labs' => $prescription->labs->map(fn ($lab) => [
                'id' => $lab->id,
                'name' => $lab->name,
                'name_ar' => $lab->name_ar,
                'code' => $lab->code,
                'fasting_required' => $lab->fasting_required,
                'preparation' => $lab->preparation,
                'notes' => $lab->pivot->notes,
            ])->values(),
            'scans' => $prescription->scans->map(fn ($scan) => [
                'id' => $scan->id,
                'name' => $scan->name,
                'name_ar' => $scan->name_ar,
                'code' => $scan->code,
                'with_contrast' => $scan->with_contrast,
                'preparation' => $scan->preparation,
                'notes' => $scan->pivot->notes,
            ])->values(),
            'created_at' => $prescription->created_at?->toDateTimeString(),
            'updated_at' => $prescription->updated_at?->toDateTimeString(),
        ];
    }
}
