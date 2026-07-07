<?php

namespace App\Enums;

enum AppointmentGrade: string
{
    case DONEINTIME = 'Done in Time';
    case DELAYEDBYDOCTOR = 'Delayed by Doctor';
    case DELAYERBYPATIENT = 'Delayed by Patient';
    case CANCLEDBYDOCTOR = 'Cancled by Doctor';
    case CANCLEDBYPATIENT = 'Cancled by Patient';
}
