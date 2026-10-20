<?php

namespace App\APIServices\WhatsApp\Services;

use App\Models\Prescription;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

class FormatPrescriptionToMessage
{
    public static function execute(Prescription $prescription): string
    {
        $prescription->loadMissing(['appointment', 'medicines', 'labs', 'scans']);

        $lines = ['*الروشتة*'];

        if ($prescription->appointment) {
            $lines[] = 'كشف '.ArabicDateFormatter::format(Carbon::parse($prescription->appointment->date), includeTime: false);
        }

        if ($prescription->diagnosis) {
            $lines[] = "\n*التشخيص*\n".$prescription->diagnosis;
        }

        if ($prescription->medicines->isNotEmpty()) {
            $lines[] = "\n*الأدوية*";

            foreach ($prescription->medicines as $index => $medicine) {
                $lines[] = ArabicDateFormatter::toArabicDigits($index + 1).'. '.$medicine->name;

                $dosing = array_filter([
                    $medicine->pivot->dose,
                    $medicine->pivot->frequency,
                    $medicine->pivot->duration,
                ]);

                if ($dosing) {
                    $lines[] = '   '.implode(' - ', $dosing);
                }

                if ($medicine->pivot->instructions) {
                    $lines[] = '   '.$medicine->pivot->instructions;
                }
            }
        }

        if ($prescription->labs->isNotEmpty()) {
            $lines[] = "\n*التحاليل*";

            foreach ($prescription->labs as $lab) {
                $lines[] = '- '.($lab->name_ar ?: $lab->name)
                    .($lab->fasting_required ? ' (صائم)' : '')
                    .($lab->pivot->notes ? ': '.$lab->pivot->notes : '');
            }
        }

        if ($prescription->scans->isNotEmpty()) {
            $lines[] = "\n*الأشعة*";

            foreach ($prescription->scans as $scan) {
                $lines[] = '- '.($scan->name_ar ?: $scan->name)
                    .($scan->pivot->notes ? ': '.$scan->pivot->notes : '');
            }
        }

        if ($prescription->notes) {
            $lines[] = "\n*ملاحظات*\n".$prescription->notes;
        }

        if ($prescription->consultation_date) {
            $lines[] = "\n*موعد الاستشارة*\n".ArabicDateFormatter::format($prescription->consultation_date, includeTime: false);
        }

        return implode("\n", $lines);
    }
}
