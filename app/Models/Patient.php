<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;
use App\Models\Note;
use App\Models\Flag;
use App\Models\User;

class Patient extends Model
{
    protected $fillable = ['user_id'];

    /**
     * The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($patient) {
            if ($patient->user) {
                $patient->user->delete();
            }
        });
    }

    /**
     * Get the user that owns the patient profile.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the appointments for the patient.
     */
    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the notes for the patient.
     */
    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the flags associated with the patient.
     */
    public function flags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Flag::class, 'flag_patient')
                    ->withPivot('doctor_id')
                    ->withTimestamps();
    }
}
