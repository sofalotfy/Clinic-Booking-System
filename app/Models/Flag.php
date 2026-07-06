<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flag extends Model
{
    protected $fillable = ['doctor_id', 'name', 'color', 'description'];

    /**
     * Get the doctor that created the flag.
     */
    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the patients associated with the flag.
     */
    public function patients(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'flag_patient')
                    ->withPivot('doctor_id')
                    ->withTimestamps();
    }
}
