<?php

namespace App\Enums;

enum UserType: string
{
    case PATIENT = 'Patient';
    case DOCTOR = 'Doctor';
}
