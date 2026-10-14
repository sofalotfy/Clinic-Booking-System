<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{

    protected $guarded = [];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function isAdmin(): bool
    {
        return $this->doctor_id === null;
    }

    public function isClinic(): bool
    {
        return $this->doctor_id !== null;
    }
}