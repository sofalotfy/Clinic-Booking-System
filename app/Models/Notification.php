<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\NotificationsType;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $guarded = [];
}
