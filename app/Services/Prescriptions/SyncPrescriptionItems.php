<?php

namespace App\Services\Prescriptions;

use App\Models\Prescription;

class SyncPrescriptionItems
{
    /**
     * Replaces the medicines, labs and scans whose key is present in $data.
     * A missing key leaves that list as it is, an empty array clears it.
     */
    public static function execute(Prescription $prescription, array $data): void
    {
        if (array_key_exists('medicines', $data)) {
            $prescription->medicines()->sync(self::keyed($data['medicines'], ['dose', 'frequency', 'duration', 'instructions']));
        }

        if (array_key_exists('labs', $data)) {
            $prescription->labs()->sync(self::keyed($data['labs'], ['notes']));
        }

        if (array_key_exists('scans', $data)) {
            $prescription->scans()->sync(self::keyed($data['scans'], ['notes']));
        }
    }

    /**
     * [['id' => 5, 'dose' => ...], ...] => [5 => ['dose' => ...], ...]
     */
    private static function keyed(?array $items, array $pivotFields): array
    {
        $keyed = [];

        foreach ($items ?? [] as $item) {
            $keyed[$item['id']] = array_map(
                fn ($field) => $item[$field] ?? null,
                array_combine($pivotFields, $pivotFields)
            );
        }

        return $keyed;
    }
}
