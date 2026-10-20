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
    ];

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
