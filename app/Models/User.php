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
use App\Enums\UserType;
use Laravel\Sanctum\HasApiTokens;
use App\Services\Clinics\GetClinicDoctor;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $guarded = [];

    protected ?array $adminPermissionNamesCache = null;
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
            'type' => UserType::class,
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

    public function profile()
    {
        return match ($this->type) {
            UserType::ASSISTANT => $this->hasOne(Assistant::class),
            UserType::DOCTOR    => $this->hasOne(Doctor::class),
            UserType::PATIENT   => $this->hasOne(Patient::class),
            UserType::ADMIN     => $this->hasOne(Admin::class),
        };
    }

    public function assistant()
    {
        return $this->hasOne(Assistant::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function isAdmin(): bool
    {
        return $this->type === UserType::ADMIN;
    }

    /**
     * The platform (type = admin) permission names the user holds, whether
     * assigned directly or inherited through one of their roles. Resolved by
     * type so a same-named clinic permission can never satisfy an admin check.
     *
     * @return array<int, string>
     */
    public function adminPermissionNames(): array
    {
        if (! $this->isAdmin()) {
            return [];
        }

        return $this->adminPermissionNamesCache ??= $this->getAllPermissions()
            ->where('type', \App\Enums\PermissionsTypeEnum::ADMIN->value)
            ->pluck('name')
            ->all();
    }

    public function hasAdminPermission(\App\Enums\AdminPermissionsEnum|string $permission): bool
    {
        $name = $permission instanceof \App\Enums\AdminPermissionsEnum ? $permission->value : $permission;

        return in_array($name, $this->adminPermissionNames(), true);
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
        return $this->doctor ?? $this->patient ?? $this->admin;
    }

    public function clinicDoctor()
    {
        return GetClinicDoctor::execute($this);
    }

    public function clinicDoctorId()
    {
        return $this->clinicDoctor()?->id;
    }

    /**
     * Whether the user is a doctor, or an assistant of a doctor, that is
     * currently suspended from the platform.
     */
    public function isClinicSuspended(): bool
    {
        return (bool) $this->clinicDoctor()?->isSuspended();
    }

    public function receivedNotifications()
    {
        if ($this->isPatient()) {
            return Notification::whereRaw('0 = 1');
        }

        return $this->hasMany(Notification::class, 'receiver_id')
            ->where('viewed', false)
            ->latest();
    }
}
