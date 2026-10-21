<?php

namespace App\APIServices\Prescriptions;

use App\Models\Appointment;
use App\Models\Prescription;
use App\Services\DaysInstances\Retrievals\GetAvailableDays;
use Carbon\Carbon;
use Closure;
use Illuminate\Validation\Rule;

class PrescriptionRules
{
    private const MAX_ITEMS = 50;

    public static function rules(Appointment $appointment, ?Prescription $existing = null): array
    {
        $appointmentDay = Carbon::parse($appointment->date)->toDateString();

        return [
            'diagnosis' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
            // The follow-up visit: after the appointment's day, on a day the doctor works
            'consultation_date' => [
                'nullable',
                'bail',
                'date_format:Y-m-d',
                'after:'.$appointmentDay,
                self::availableDay($appointment->doctor_id, $existing?->consultation_date?->toDateString()),
            ],

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

    /**
     * The day must be open for booking: scheduled, not cancelled and not full.
     *
     * Two cases are let through. A date further out than the schedule is
     * generated for cannot be checked yet, so it is accepted. A date the
     * prescription already has is not checked again on update, since the
     * day filling up since then is no reason to refuse an unrelated edit.
     */
    private static function availableDay(int $doctorId, ?string $currentDate): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($doctorId, $currentDate) {
            if ($value === $currentDate) {
                return;
            }

            [, $windowEnd] = GetAvailableDays::window();

            if (Carbon::parse($value)->gt($windowEnd)) {
                return;
            }

            $available = collect(GetAvailableDays::execute($doctorId))
                ->map(fn ($day) => Carbon::parse($day['date'])->toDateString());

            if (! $available->contains($value)) {
                $fail('The doctor is not available on this day, please choose another date.');
            }
        };
    }

    public static function messages(): array
    {
        return [
            'consultation_date.after' => 'The consultation date must be after the appointment date.',
            '*.*.id.distinct' => 'The same item is listed twice.',
        ];
    }
}
