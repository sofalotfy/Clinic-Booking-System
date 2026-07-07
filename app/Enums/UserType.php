<?php

namespace App\Enums;

enum UserType: string
{
    case PATIENT = 'patient';
    case DOCTOR = 'doctor';
}
