<?php

namespace App\Enums;

enum AppointmentGrade: string
{
    case DONEINTIME = 'Done in Time';
    case DOCTORDELAY = 'Doctor Delay';
    case PATIENTDELAY = 'Patient Delay';
    case DOCTORCANCEL = 'Doctor Cancel';
    case PATIENTCANCEL = 'Patient Cancel';
}
