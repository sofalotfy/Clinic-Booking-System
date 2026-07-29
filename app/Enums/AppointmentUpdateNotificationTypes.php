<?php

namespace App\Enums;

enum AppointmentUpdateNotificationTypes: string
{
    case COLIDE = 'Colide';
    case CANCEL = 'Cancel';
    case TRUNCATE = 'Truncate';
    case OVERFLOW = 'Overflow';
    case RESHEEDULE = 'Reshedule';
}
