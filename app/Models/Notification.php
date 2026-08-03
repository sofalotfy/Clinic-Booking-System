<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\NotificationsType;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'text',
        'route',
        'type',
        'user_id',
        'viewed',
    ];

    protected $casts = [
        'type' => NotificationsType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public $timestamps = true;
}
