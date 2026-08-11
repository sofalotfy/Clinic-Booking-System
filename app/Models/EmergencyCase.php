<?php

namespace App\Models;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class EmergencyCase extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'context',
        'symptoms',
        'latitude',
        'longitude',
        'place',
        'address',
        'in_hospital',
        'hospital_name',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
