<?php

namespace App\Enums;

enum ScanModality: string
{
    case X_RAY = 'X-Ray';
    case CT = 'CT';
    case MRI = 'MRI';
    case ULTRASOUND = 'Ultrasound';
    case DOPPLER = 'Doppler';
    case MAMMOGRAPHY = 'Mammography';
    case FLUOROSCOPY = 'Fluoroscopy';
    case DEXA = 'DEXA';
    case PET = 'PET';
    case NUCLEAR = 'Nuclear Medicine';
    case OTHER = 'Other';
}
