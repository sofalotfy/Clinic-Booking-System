<?php

namespace App\Models;

use App\Enums\AppointmentGrade;
use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    public $old_date;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'type' => AppointmentType::class,
            'grade' => AppointmentGrade::class,
        ];
    }

    /**
     * Get the doctor for this appointment.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the patient for this appointment.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', AppointmentStatus::working());
    }
}
