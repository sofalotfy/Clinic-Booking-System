<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;

class Day extends Model
{
    protected $fillable = ['doctor_id', 'date', 'start_time', 'end_time', 'appointment_duration', 'queue_length'];

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
