<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentGrade;

class Appointment extends Model
{
    protected $fillable = ['doctor_id', 'patient_id', 'status', 'date', 'delay', 'duration', 'grade'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'grade' => AppointmentGrade::class,
        ];
    }

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
