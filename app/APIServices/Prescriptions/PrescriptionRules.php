<?php

namespace App\APIServices\Prescriptions;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class PrescriptionRules
{
    private const MAX_ITEMS = 50;

    public static function rules(Appointment $appointment): array
    {
        $appointmentDay = Carbon::parse($appointment->date)->toDateString();

        return [
            'diagnosis' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
            // The follow-up visit, so it has to fall after the appointment's day
            'consultation_date' => 'nullable|date_format:Y-m-d|after:'.$appointmentDay,

            'medicines' => 'sometimes|nullable|array|max:'.self::MAX_ITEMS,
            'medicines.*.id' => ['required', 'integer', 'distinct', Rule::exists('medicines', 'id')->whereNull('deleted_at')],
            'medicines.*.dose' => 'nullable|string|max:255',
            'medicines.*.frequency' => 'nullable|string|max:255',
            'medicines.*.duration' => 'nullable|string|max:255',
            'medicines.*.instructions' => 'nullable|string|max:1000',

            'labs' => 'sometimes|nullable|array|max:'.self::MAX_ITEMS,
            'labs.*.id' => ['required', 'integer', 'distinct', Rule::exists('labs', 'id')->whereNull('deleted_at')],
            'labs.*.notes' => 'nullable|string|max:1000',

            'scans' => 'sometimes|nullable|array|max:'.self::MAX_ITEMS,
            'scans.*.id' => ['required', 'integer', 'distinct', Rule::exists('scans', 'id')->whereNull('deleted_at')],
            'scans.*.notes' => 'nullable|string|max:1000',
        ];
    }

    public static function messages(): array
    {
        return [
            'consultation_date.after' => 'The consultation date must be after the appointment date.',
            '*.*.id.distinct' => 'The same item is listed twice.',
        ];
    }
}
