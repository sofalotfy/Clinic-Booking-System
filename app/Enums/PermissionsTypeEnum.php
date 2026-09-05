<?php

namespace App\Enums;

enum PermissionsTypeEnum: string
{
    case ASSISTANT = 'assistant';
    case ADMIN = 'admin';
    case NOTIFICATION = 'notification';
}
