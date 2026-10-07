<?php

namespace App\AdminServices\Doctors;

use App\Enums\Gender;
use App\Services\Clinics\StoreClinic;
use App\Services\Doctors\StoreDoctor as StoreDoctorService;
use App\Services\Doctors\SyncDoctorWhatsAppAccount;
use Illuminate\Validation\Rule;

class StoreDoctor
{
    public static function execute($request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'area' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'clinic_name' => 'required|string|max:255',
            'clinic_location_link' => 'nullable|string',
            'clinic_address' => 'nullable|string',
            'clinic_facebook' => 'nullable|string',
            'clinic_instgram' => 'nullable|string',
            'clinic_linkedin' => 'nullable|string',
            'clinic_vezeeta' => 'nullable|string',
            'clinic_phone' => 'nullable|string|max:20',
            'notifications_phone' => 'nullable|string|max:20',
            'whatsapp_phone_number_id' => 'required|string',
            'whatsapp_access_token' => 'required|string',
        ]);

        $doctor = StoreDoctorService::execute($validated, $request);

        StoreClinic::execute(null, [
            'name' => $validated['clinic_name'],
            'location_link' => $validated['clinic_location_link'] ?? null,
            'address' => $validated['clinic_address'] ?? null,
            'facebook' => $validated['clinic_facebook'] ?? null,
            'instgram' => $validated['clinic_instgram'] ?? null,
            'linkedin' => $validated['clinic_linkedin'] ?? null,
            'vezeeta' => $validated['clinic_vezeeta'] ?? null,
            'clinic_phone' => $validated['clinic_phone'] ?? null,
            'notifications_phone' => $validated['notifications_phone'] ?? null,
        ], $doctor->id);

        SyncDoctorWhatsAppAccount::execute($doctor, [
            'phone_number_id' => $validated['whatsapp_phone_number_id'],
            'access_token' => $validated['whatsapp_access_token'],
            'is_active' => true,
        ]);

        return $doctor->load(['user', 'clinic', 'whatsappAccount']);
    }
}
