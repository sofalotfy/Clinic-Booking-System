<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientInquiry extends Model
{
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'question',        
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
