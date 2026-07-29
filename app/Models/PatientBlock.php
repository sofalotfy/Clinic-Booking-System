<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientBlock extends Model
{
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'blocked_by',
        'reason',
        'blocked_at',
        'expires_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null &&
            $this->expires_at->isPast();
    }
}