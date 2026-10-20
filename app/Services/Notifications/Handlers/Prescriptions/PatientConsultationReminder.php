<?php

namespace App\Services\Notifications\Handlers\Prescriptions;

use App\APIServices\WhatsApp\States\Notifications\ConsultationReminder;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\Channels\SendWhatsAppStatefulNotification;
use App\Services\Notifications\Handlers\Handler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Reminds the patient of the consultation set on their prescription (the
 * model), one week and one day before it. Handles both reminder types.
 */
class PatientConsultationReminder extends Handler
{
    public static function execute(User $sender, int $clinicId, $notification, Collection $receivers, Model $model)
    {
        $title = $notification->title();
        $body = $notification->body(['date' => $model->consultation_date]);

        static::dispatch($sender, $clinicId, $notification, $receivers, $model, $title, $body);
    }

    protected static function sendWhatsApp(User $sender, array $receiver, int $clinicId, $notification, $model, string $title, string $body, ?Notification $systemNotification = null)
    {
        if (! $receiver['user']) {
            return null;
        }

        return SendWhatsAppStatefulNotification::execute(
            $sender,
            $receiver['user'],
            $clinicId,
            $notification,
            [
                ConsultationReminder::KEY_PRESCRIPTION_ID => $model->id,
                ConsultationReminder::KEY_CONSULTATION_DATE => $model->consultation_date->toDateString(),
            ],
            $systemNotification?->id
        );
    }
}
