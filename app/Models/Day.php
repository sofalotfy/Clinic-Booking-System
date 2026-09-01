<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;

class Day extends Model
{
    protected $guarded = [];

    protected $casts = [
        'appointment_duration' => 'integer',
    ];

    /**
     * Get the doctor for this day schedule.
     */
    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
