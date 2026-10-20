<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prescription extends Model
{
    protected $guarded = [];

    protected $casts = [
        'consultation_date' => 'date:Y-m-d',
        'week_reminder_sent_at' => 'datetime',
        'day_reminder_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // A moved consultation needs both reminders again for the new date
        static::updating(function (Prescription $prescription) {
            if ($prescription->isDirty('consultation_date')) {
                $prescription->week_reminder_sent_at = null;
                $prescription->day_reminder_sent_at = null;
            }
        });
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * withTrashed: a medicine removed from the catalog still shows on the
     * prescriptions it was written on. Ordered as the doctor entered them.
     */
    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(Medicine::class)
            ->withTrashed()
            ->withPivot(['dose', 'frequency', 'duration', 'instructions'])
            ->withTimestamps()
            ->orderByPivot('id');
    }

    public function labs(): BelongsToMany
    {
        return $this->belongsToMany(Lab::class)
            ->withTrashed()
            ->withPivot('notes')
            ->withTimestamps()
            ->orderByPivot('id');
    }

    public function scans(): BelongsToMany
    {
        return $this->belongsToMany(Scan::class)
            ->withTrashed()
            ->withPivot('notes')
            ->withTimestamps()
            ->orderByPivot('id');
    }
}
