<?php

namespace App\AdminServices\Doctors;

use App\Enums\Gender;
use App\Models\Doctor;
use App\Services\Clinics\StoreClinic;
use App\Services\Clinics\UpdateClinic;
use App\Services\Doctors\SyncDoctorWhatsAppAccount;
use App\Services\Doctors\UpdateDoctor as UpdateDoctorService;
use Illuminate\Validation\Rule;

class UpdateDoctor
{
    public static function execute($request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$doctor->user_id,
            'phone' => 'nullable|string|max:20|unique:users,phone,'.$doctor->user_id,
            'password' => 'nullable|string|min:8',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'area' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'clinic_name' => 'nullable|string|max:255',
            'clinic_location_link' => 'nullable|string',
            'clinic_address' => 'nullable|string',
            'clinic_facebook' => 'nullable|string',
            'clinic_instgram' => 'nullable|string',
            'clinic_linkedin' => 'nullable|string',
            'clinic_vezeeta' => 'nullable|string',
            'clinic_phone' => 'nullable|string|max:20',
            'notifications_phone' => 'nullable|string|max:20',
            'whatsapp_phone_number_id' => 'nullable|string',
            'whatsapp_access_token' => 'nullable|string',
            'whatsapp_is_active' => 'nullable|in:0,1',
        ]);

        UpdateDoctorService::execute($doctor, $validated, $request);

        if (! empty($validated['clinic_name'])) {
            $clinicData = [
                'name' => $validated['clinic_name'],
                'location_link' => $validated['clinic_location_link'] ?? null,
                'address' => $validated['clinic_address'] ?? null,
                'facebook' => $validated['clinic_facebook'] ?? null,
                'instgram' => $validated['clinic_instgram'] ?? null,
                'linkedin' => $validated['clinic_linkedin'] ?? null,
                'vezeeta' => $validated['clinic_vezeeta'] ?? null,
                'clinic_phone' => $validated['clinic_phone'] ?? null,
                'notifications_phone' => $validated['notifications_phone'] ?? null,
            ];

            if ($doctor->clinic) {
                UpdateClinic::execute($doctor->clinic, $clinicData);
            } else {
                StoreClinic::execute(null, $clinicData, $doctor->id);
            }
        }

        $whatsappData = [];

        foreach (['whatsapp_phone_number_id' => 'phone_number_id', 'whatsapp_access_token' => 'access_token'] as $field => $column) {
            if (! empty($validated[$field])) {
                $whatsappData[$column] = $validated[$field];
            }
        }

        if (array_key_exists('whatsapp_is_active', $validated)) {
            $whatsappData['is_active'] = (bool) $validated['whatsapp_is_active'];
        }

        if (! empty($whatsappData)) {
            SyncDoctorWhatsAppAccount::execute($doctor, $whatsappData);
        }

        return $doctor->load(['user', 'clinic', 'whatsappAccount']);
    }
}
