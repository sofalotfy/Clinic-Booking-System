<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\Gender;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'image', 'phone', 'age', 'gender', 'address'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'gender' => Gender::class,
        ];
    }

    /**
     * Get the doctor profile associated with the user.
     */
    public function doctor(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Get the patient profile associated with the user.
     */
    public function patient(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Patient::class);
    }

    /**
     * Determine if the user is a doctor.
     */
    public function isDoctor(): bool
    {
        return $this->doctor()->exists();
    }

    /**
     * Determine if the user is a patient.
     */
    public function isPatient(): bool
    {
        return $this->patient()->exists();
    }

    /**
     * Get the profile (doctor or patient) of the user.
     */
    public function getProfileAttribute()
    {
        return $this->doctor ?? $this->patient;
    }

    /**
     * Get the type of the user ('doctor', 'patient', or 'unknown').
     */
    public function getTypeAttribute(): string
    {
        if ($this->doctor()->exists()) {
            return 'doctor';
        }
        if ($this->patient()->exists()) {
            return 'patient';
        }
        return 'unknown';
    }
}
