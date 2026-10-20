<?php

namespace App\AdminServices\Medicines;

use App\Models\Medicine;
use App\Services\Medicines\StoreMedicine as StoreMedicineService;
use Closure;

class StoreMedicine
{
    public static function execute($request)
    {
        return StoreMedicineService::execute($request->validate(self::rules()));
    }

    public static function rules(?Medicine $medicine = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Trade names are matched like the sheet import does, deleted ones included
                function (string $attribute, $value, Closure $fail) use ($medicine) {
                    $taken = Medicine::withTrashed()
                        ->where('match_key', Medicine::matchKey($value))
                        ->when($medicine, fn ($query) => $query->whereKeyNot($medicine->id))
                        ->exists();

                    if ($taken) {
                        $fail('A medicine with this trade name already exists (it may be deleted).');
                    }
                },
            ],
            'name_ar' => 'nullable|string|max:255',
            'active_ingredient' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'major_units' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'minor_units' => 'nullable|integer|min:0',
            'category' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'image_url' => 'nullable|url|max:2048',
            'is_active' => 'boolean',
        ];
    }
}
