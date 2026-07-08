<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case ACTIVE = 'Active';
    case DONE = 'Done';
    case CANCELLED = 'Cancelled';
    case QUEUED = 'Queued';
}
