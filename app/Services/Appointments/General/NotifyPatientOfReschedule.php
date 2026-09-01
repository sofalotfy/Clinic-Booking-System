<?php

namespace App\Services\Appointments\General;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\WhatsAppConversation;
use App\Enums\ConversationState;
use Carbon\Carbon;
use App\APIServices\WhatsApp\ExecutionRouter;
use App\Enums\AppointmentUpdateNotificationTypes;
class NotifyPatientOfReschedule
{
    public static function execute($user, $appointment, $new_date = null, $type = AppointmentUpdateNotificationTypes::COLIDE)
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
            'data' => [
                'name' =>  $patient->user->name,
                'appointment_id' => $appointment->id,
                'reschedule_type' => $type,
                'new_date' => $new_date,
            ],
            'last_activity_at' => now(),
        ]);

        // 6. Hand off to the router
        ExecutionRouter::execute(
            $conversation
        );
    }
}