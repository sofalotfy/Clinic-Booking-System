<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\WhatsAppConversation;
use App\Enums\ConversationState;
use Carbon\Carbon;
use App\APIServices\WhatsApp\ExecutionRouter;

class NotifyReshedule
{
    public static function execute($appointment, $new_date = null, $type = 'collide')
    {
        $conversation = WhatsAppConversation::where('patient_id',$appointment->patient_id)->first();
        $patient = Patient::find($appointment->patient_id);
        if(!$conversation){
            // 4. Find or create the conversation
            $conversation = WhatsAppConversation::Create(
                [
                    'doctor_whatsapp_account_id' => $appointment->doctor_id,
                    'phone_number' => $patient->user->phone,
                    'patient_id' => $patient->id,
                ]
            );
        }
        $conversation->update([
            'state'=> ConversationState::CONFIRM_RESHEDULE,
            'step' => null,
            'data' => [
                'name' =>  $patient->user->name,
                'appointment_id' => $appointment->id,
                'reschedule_type' => $type,
                'new_date' => $new_date,
            ],
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        // 6. Hand off to the router
        ExecutionRouter::execute(
            $conversation
        );
    }
}