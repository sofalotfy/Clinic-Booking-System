<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['doctor_id', 'patient_id', 'status', 'date', 'delay', 'duration', 'grade'];

    /**
     * Get the doctor for this appointment.
     */
    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the patient for this appointment.
     */
    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
