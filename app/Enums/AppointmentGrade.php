<?php

namespace App\Enums;

enum AppointmentGrade: string
{
    case EXCELLENT = 'excellent';
    case GOOD = 'good';
    case AVERAGE = 'average';
    case POOR = 'poor';
}
