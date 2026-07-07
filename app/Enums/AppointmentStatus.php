<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case ACTIVE = 'active';
    case DONE = 'done';
    case CANCELLED = 'cancelled';
    case QUEUED = 'queued';
}
