<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case ACTIVE = 'Active';
    case DONE = 'Done';
    case CANCELLED = 'Cancelled';
    case QUEUED = 'Queued';
    case PENDING = 'Pending';

    public static function working(): array
    {
        return [
            self::ACTIVE,
            self::PENDING,
            self::QUEUED,
        ];
    }

    public static function free(): array
    {
        return [
            self::DONE,
            self::CANCELLED,
        ];
    }
}
