<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['doctor_id', 'patient_id', 'text'];

    /**
     * Get the doctor that wrote the note.
     */
    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the patient that the note is about.
     */
    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
