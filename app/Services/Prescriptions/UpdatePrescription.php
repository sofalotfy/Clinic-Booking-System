<?php

namespace App\Services\Prescriptions;

use App\Models\Prescription;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdatePrescription
{
    public static function execute(Prescription $prescription, array $data): Prescription
    {
        return DB::transaction(function () use ($prescription, $data) {
            $prescription->update(Arr::only($data, ['diagnosis', 'notes', 'consultation_date']));

            SyncPrescriptionItems::execute($prescription, $data);

            return ShowPrescription::execute($prescription);
        });
    }
}
