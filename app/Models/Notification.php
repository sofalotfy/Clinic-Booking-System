<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'viewed' => 'boolean',
            'status' => NotificationStatus::class,
        ];
    }

    /**
     * Get the user that triggered the notification.
     */
    public function sender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user the notification was delivered to.
     */
    public function receiver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the clinic (doctor profile) the notification belongs to.
     */
    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('viewed', false);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('receiver_id', $user->id);
    }

    public function markViewed(): self
    {
        $this->forceFill(['viewed' => true])->save();

        return $this;
    }

    public function markSent(): self
    {
        $this->forceFill([
            'status' => NotificationStatus::SENT,
            'failure_reason' => null,
        ])->save();

        return $this;
    }

    public function markFailed(?string $reason = null): self
    {
        $this->forceFill([
            'status' => NotificationStatus::FAILED,
            'failure_reason' => $reason,
        ])->save();

        return $this;
    }
}
