<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Formats dates into the Arabic pattern used in the WhatsApp bot messages:
 *   {day name} {day# (Arabic-Indic digits)} {month name} الساعة {hour} {مساء/صباحا}
 * e.g. "الأربعاء ١٢ مايو الساعة ٩ مساء"  ==  Wednesday 12 May at 9 PM
 *
 * Notes on the pattern (confirmed from the source PDF):
 * - Digits are Eastern Arabic numerals, not Western.
 * - Month is the Gregorian month name spelled in Arabic (not Hijri).
 * - Year is never included.
 * - Minutes are never included — always rounds to the hour in the examples.
 * - One message in the source (appointment cancellation confirm) omits the
 *   time portion entirely — use format($date, includeTime: false) for that case.
 */
class ArabicDateFormatter
{
    protected const DAYS = [
        0 => 'الأحد',
        1 => 'الإثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];

    protected const MONTHS = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
    ];

    protected const DIGIT_MAP = [
        '0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
        '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩',
    ];

    /**
     * Convert Western digits (in an int or string) to Eastern Arabic numerals.
     */
    public static function toArabicDigits(int|string $value): string
    {
        return strtr((string) $value, self::DIGIT_MAP);
    }

    /**
     * Get the Arabic name of the day by its integer value (0 for Sunday, 6 for Saturday).
     */
    public static function getDayName(int $dayOfWeek): string
    {
        return self::DAYS[$dayOfWeek] ?? '';
    }


    /**
     * Format a Carbon date into the Arabic WhatsApp-message style.
     */
    public static function format(Carbon $date, bool $includeTime = true): string
    {
        $dayName   = self::DAYS[$date->dayOfWeek];
        $dayNumber = self::toArabicDigits($date->day);
        $monthName = self::MONTHS[$date->month];

        $formatted = "{$dayName} {$dayNumber} {$monthName}";

        if ($includeTime) {
            $hour12       = (int) $date->format('g'); // 1-12, no leading zero
            $hourArabic   = self::toArabicDigits($hour12);
            $period       = $date->format('A') === 'AM' ? 'صباحا' : 'مساء';
            $formatted   .= " الساعة {$hourArabic} {$period}";
        }

        return $formatted;
    }

    public static function formatTime(string $time): string
    {
        $date = Carbon::createFromFormat('H:i', $time);

        $hour = self::toArabicDigits((int) $date->format('g'));
        $minutes = self::toArabicDigits($date->format('i'));

        $period = $date->format('A') === 'AM'
            ? 'صباحا'
            : 'مساء';

        return "{$hour}:{$minutes} {$period}";
    }
}

/*
Usage example:

use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

$date = Carbon::create(2026, 5, 13, 21, 0); // a Wednesday, 9 PM

ArabicDateFormatter::format($date);
// => "الأربعاء ١٣ مايو الساعة ٩ مساء"

ArabicDateFormatter::format($date, includeTime: false);
// => "الأربعاء ١٣ مايو"

Then feed it into the lang file:

__('whatsapp.appointment_booked.message', [
    'name' => $patientName,
    'date' => ArabicDateFormatter::format($appointmentDate),
]);
*/