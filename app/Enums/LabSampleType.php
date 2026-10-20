<?php

namespace App\Enums;

enum LabSampleType: string
{
    case BLOOD = 'Blood';
    case URINE = 'Urine';
    case STOOL = 'Stool';
    case SWAB = 'Swab';
    case SALIVA = 'Saliva';
    case SPUTUM = 'Sputum';
    case CSF = 'CSF';
    case SEMEN = 'Semen';
    case TISSUE = 'Tissue';
    case OTHER = 'Other';
}
