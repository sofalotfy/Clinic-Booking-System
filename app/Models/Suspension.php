<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suspension extends Model
{
    protected $guarded = [];

    protected $casts = [
        'suspended_at' => 'datetime',
        'lifted_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function suspender()
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function lifter()
    {
        return $this->belongsTo(User::class, 'lifted_by');
    }

    public function isActive(): bool
    {
        return $this->lifted_at === null;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('lifted_at');
    }
}
