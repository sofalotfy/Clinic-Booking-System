<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Formats dates into the Arabic pattern used in the WhatsApp bot messages:
 *   {day name} {day# (Arabic-Indic digits)} {month name} الساعة {time} {صباحا/مساء}
 * e.g. "الأربعاء ١٢ مايو الساعة ٩ مساء"  ==  Wednesday 12 May at 9 PM
 *      "الأربعاء ١٢ مايو الساعة ٩:٣٠ مساء" ==  Wednesday 12 May at 9:30 PM
 *
 * - Digits are Eastern Arabic numerals.
 * - Month is the Gregorian month name in Arabic.
 * - Year is never included.
 * - Minutes are shown only when they are not zero.
 * - Use format($date, includeTime: false) for date-only values (e.g. queued appointments).
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
            $hour12     = (int) $date->format('g'); // 1-12, no leading zero
            $minutes    = (int) $date->format('i');
            $hourArabic = self::toArabicDigits($hour12);
            $period     = $date->format('A') === 'AM' ? 'صباحا' : 'مساء';

            // Show minutes only when they aren't zero
            $time = $minutes > 0
                ? $hourArabic . ':' . self::toArabicDigits($date->format('i'))
                : $hourArabic;

            $formatted .= " الساعة {$time} {$period}";
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