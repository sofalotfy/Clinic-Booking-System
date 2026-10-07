<?php

namespace App\Services\Doctors;

use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;

class SyncDoctorWhatsAppAccount
{
    public static function execute(Doctor $doctor, array $data)
    {
        $account = $doctor->whatsappAccount;

        if (! $account) {
            if (empty($data['phone_number_id']) || empty($data['access_token'])) {
                return null;
            }

            return DoctorWhatsAppAccount::create([
                'doctor_id' => $doctor->id,
                'phone_number_id' => $data['phone_number_id'],
                'access_token' => $data['access_token'],
                'is_active' => $data['is_active'] ?? true,
            ]);
        }

        $account->update(array_filter([
            'phone_number_id' => $data['phone_number_id'] ?? $account->phone_number_id,
            'access_token' => $data['access_token'] ?? $account->access_token,
            'is_active' => array_key_exists('is_active', $data) ? $data['is_active'] : $account->is_active,
        ], fn ($value) => ! is_null($value)));

        return $account->refresh();
    }
}
