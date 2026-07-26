<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorWhatsAppAccount;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Flag;
use App\Models\Note;
use App\Models\Day;
use App\Models\TemplateDay;
use App\Models\TemplatePlan;


class Doctor extends Model
{
    protected $fillable = ['user_id'];

    /**
     * The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($doctor) {
            if ($doctor->user) {
                $doctor->user->delete();
            }
        });
    }

    /**
     * Get the user that owns the doctor profile.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the appointments for the doctor.
     */
    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the flags for the doctor.
     */
    public function flags(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Flag::class);
    }

    /**
     * Get the notes for the doctor.
     */
    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the days for the doctor.
     */
    public function days(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Day::class);
    }

    /**
     * Get the template days for the doctor.
     */
    public function templateDays(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TemplateDay::class);
    }

    /**
     * Get the template plans for the doctor.
     */
    public function templatePlans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TemplatePlan::class);
    }

    public function whatsappAccount()
    {
        return $this->hasOne(DoctorWhatsAppAccount::class);
    }

    public function assistants()
    {
        return $this->hasMany(Assistant::class);
    }
}
