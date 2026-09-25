<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Enums\DayStatus;

class Day extends Model
{
    protected $guarded = [];

    protected $casts = [
        'appointment_duration' => 'integer',
        'status' => DayStatus::class,
    ];

    /**
     * Get the doctor for this day schedule.
     */
    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'doctor_id')
            ->whereDate('date', $this->date);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', DayStatus::working());
    }
    
    public function scopeinActive($query)
    {
        return $query->whereIn('status', DayStatus::closed());
    }

    public function isActive()
    {
        return $this->status === DayStatus::ACTIVE;
    }
}
