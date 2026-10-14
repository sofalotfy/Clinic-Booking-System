<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Enums\DayStatus;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Day extends Model
{
    protected $guarded = [];

    protected $casts = [
        'appointment_duration' => 'integer',
        'status' => DayStatus::class,
    ];

    /**
     * Get the doctor for this day schedule.
     */
    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'doctor_id')
            ->whereDate('date', $this->date);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', DayStatus::working());
    }
    
    public function scopeinActive($query)
    {
        return $query->whereIn('status', DayStatus::closed());
    }

    public function isActive(): bool
    {
        return $this->status === DayStatus::ACTIVE;
    }

    /**
     * The working (confirmed) appointments for this day, ordered by time.
     */
    public function getAppointments(): Collection
    {
        return $this->appointments()
            ->active()
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * The number of slots still open for booking on this day. Pass the already
     * fetched appointments count to avoid an extra query.
     */
    public function getEmptySlots(?int $booked = null): int
    {
        $booked ??= $this->getAppointments()->count();

        $start = Carbon::parse($this->date.' '.$this->start_time);
        $end = Carbon::parse($this->date.' '.$this->end_time);

        $totalSlots = intdiv($start->diffInMinutes($end), $this->appointment_duration);

        return max(0, ($totalSlots + $this->queue_length) - $booked);
    }

    /**
     * The day's first appointment time, falling back to the schedule start.
     */
    public function getFirstAppointmentTime(?Collection $appointments = null): string
    {
        $appointments ??= $this->getAppointments();

        $time = $appointments->isNotEmpty()
            ? Carbon::parse($appointments->first()->date)->format('H:i')
            : Carbon::parse($this->start_time)->format('H:i');

        return ArabicDateFormatter::formatTime($time);
    }
}
