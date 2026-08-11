<?php

namespace App\Enums;

enum NotificationsType: string
{
    case GENERAL = "general";
    case PATIENT_PROFILE = 'patient_profile';
    case APPOINTMENTS = 'appointments';
    case SCHEDULE = 'schedule';
    case MEDICAL_RECORDS = 'medical_records';
    case PAYMENTS = 'payments';
    case BILLING = 'billing';
    case MESSAGES = 'messages';
    case STAFF = 'staff';
    case SYSTEM = 'system';
    case SECURITY = 'security';
    case EMERGENCY = 'emergency';
    case INQUIRY = 'inquiry';
}