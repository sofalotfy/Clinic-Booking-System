<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;
use App\Services\EmergencyCases\CreateEmergencyCase;
use App\Services\EmergencyCases\UpdateEmergencyCase;

class FileEmergencyCase
{
    private const STEP_SYMPTOMS = 'symptoms';
    private const STEP_LOCATION_TYPE = 'location_type';
    private const STEP_LOCATION = 'location';
    private const STEP_HOSPITAL_NAME = 'hospital_name';

    private const ELSE = 'else';
    private const HOSPITAL = 'hospital';

    public static function execute(
        WhatsAppConversation $conversation,
        array $message
    ) {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $step = $conversation->step ?? self::STEP_SYMPTOMS;

        if (!$conversation->step) {
            $conversation->update([
                'step' => self::STEP_SYMPTOMS,
            ]);
        }

        return match ($step) {
            self::STEP_SYMPTOMS => SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'من فضلك اشرح الأعراض التي تشعر بها حاليا',
            ),

            self::STEP_LOCATION_TYPE => SendMessage::buttons(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'هل أنت حاليا في أحد المستشفيات؟',
                [
                    [
                        'id' => self::HOSPITAL,
                        'title' => 'نعم',
                    ],
                    [
                        'id' => self::ELSE,
                        'title' => 'لا',
                    ],
                ]
            ),

            self::STEP_LOCATION => SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'من فضلك أرسل موقعك الحالي.',
            ),

            self::STEP_HOSPITAL_NAME => SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'من فضلك اكتب اسم المستشفى.',
            ),
        };
    }

    public static function handleResponse(
        WhatsAppConversation $conversation,
        array $message
    ) {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        switch ($conversation->step) {

            case self::STEP_SYMPTOMS:

                if (
                    $message['type'] !== 'text' ||
                    empty(trim($message['value'] ?? ''))
                ) {
                    return self::invalidResponse($conversation, $message);
                }

                CreateEmergencyCase::execute(
                    patientId: $conversation->patient()?->id,
                    doctorId: $account->doctor_id,
                    symptoms: trim($message['value']),
                );

                $conversation->update([
                    'step' => self::STEP_LOCATION_TYPE,
                ]);

                return self::execute($conversation, $message);


            case self::STEP_LOCATION_TYPE:

                if (
                    $message['type'] !== 'interactive' ||
                    empty($message['value'])
                ) {
                    return self::invalidResponse($conversation, $message);
                }

                $locationType = strtolower(trim($message['value']));

                if (!in_array($locationType, [
                    self::ELSE,
                    self::HOSPITAL,
                ])) {
                    return self::invalidResponse($conversation, $message);
                }

                $inHospital = $locationType === self::HOSPITAL;

                UpdateEmergencyCase::execute(
                    doctorId: $account->doctor_id,
                    patientId: $conversation->patient()?->id,
                    data: [
                        'in_hospital' => $inHospital,
                    ],
                );

                $conversation->update([
                    'step' => $inHospital
                        ? self::STEP_HOSPITAL_NAME
                        : self::STEP_LOCATION,
                ]);

                return self::execute($conversation, $message);


            case self::STEP_LOCATION:

                if ($message['type'] !== 'location') {
                    return self::invalidResponse($conversation, $message);
                }

                $location = $message['value'];
                \Log::info($location);
                UpdateEmergencyCase::execute(
                    doctorId: $account->doctor_id,
                    patientId: $conversation->patient()?->id,
                    data: [
                        'latitude' => $location['latitude'] ?? null,
                        'longitude' => $location['longitude'] ?? null,
                        'place' => $location['name'] ?? null,
                        'address' => $location['address'] ?? null,
                    ],
                );

                return self::finish(
                    $conversation,
                    $message
                );


            case self::STEP_HOSPITAL_NAME:

                if (
                    $message['type'] !== 'text' ||
                    empty(trim($message['value'] ?? ''))
                ) {
                    return self::invalidResponse($conversation, $message);
                }

                UpdateEmergencyCase::execute(
                    doctorId: $account->doctor_id,
                    patientId: $conversation->patient()?->id,
                    data: [
                        'hospital_name' => trim($message['value']),
                    ],
                );

                return self::finish(
                    $conversation,
                    $message
                );


            default:

                $conversation->update([
                    'step' => self::STEP_SYMPTOMS,
                ]);

                return self::execute($conversation, $message);
        }
    }

    private static function finish(
        WhatsAppConversation $conversation,
        array $message
    ) {
        $conversation->update([
            'step' => null,
            'state' => ConversationState::AI,
        ]);

        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        return SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            "تم تسجيل حالتك الطارئة\nمن فضلك اطلب المساعدة الطبية فورا أو تواصل مع خدمات الطوارئ إذا لزم الأمر.",
        );
    }

    private static function invalidResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'هذا الرد غير صالح، فضلا اختر أحد الخيارات المتاحة',
        );

        return self::execute($conversation, $message);
    }
}