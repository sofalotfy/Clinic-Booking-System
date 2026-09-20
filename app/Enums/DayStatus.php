<?php

namespace App\Enums;

enum DayStatus: string
{
    case ACTIVE = 'Active';
    case CANCELLED = 'Cancelled';

    public static function working(): array
    {
        return [
            self::ACTIVE,
        ];
    }

    public static function closed(): array
    {
        return [
            self::CANCELLED,
        ];
    }
}
