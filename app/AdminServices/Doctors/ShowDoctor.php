<?php

namespace App\AdminServices\Doctors;

use App\Models\Doctor;
use App\Services\Doctors\GetDoctorStatistics;
use App\Services\Doctors\ShowDoctor as ShowDoctorService;

class ShowDoctor
{
    public static function execute(Doctor $doctor)
    {
        $doctor = ShowDoctorService::execute($doctor);

        return [
            'id' => $doctor->id,
            'name' => $doctor->user?->name,
            'email' => $doctor->user?->email,
            'phone' => $doctor->user?->phone,
            'age' => $doctor->user?->age,
            'gender' => $doctor->user?->gender?->value,
            'area' => $doctor->user?->area,
            'image' => $doctor->user?->image,
            'image_url' => $doctor->user?->image ? asset('storage/'.$doctor->user->image) : null,
            'description' => $doctor->description,
            'created_at' => $doctor->user?->created_at?->format('Y-m-d'),
            'clinic' => $doctor->clinic ? [
                'name' => $doctor->clinic->name,
                'location_link' => $doctor->clinic->location_link,
                'location_link_url' => self::externalUrl($doctor->clinic->location_link),
                'address' => $doctor->clinic->address,
                'facebook' => $doctor->clinic->facebook,
                'instgram' => $doctor->clinic->instgram,
                'linkedin' => $doctor->clinic->linkedin,
                'vezeeta' => $doctor->clinic->vezeeta,
                'clinic_phone' => $doctor->clinic->clinic_phone,
                'notifications_phone' => $doctor->clinic->notifications_phone,
                'created_at' => $doctor->clinic->created_at?->format('Y-m-d'),
            ] : null,
            'whatsapp' => $doctor->whatsappAccount ? [
                'phone_number_id' => $doctor->whatsappAccount->phone_number_id,
                'is_active' => $doctor->whatsappAccount->is_active,
            ] : null,
            'stats' => GetDoctorStatistics::execute($doctor),
        ];
    }

    private static function externalUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (! preg_match('#^https?://#i', $url)) {
            return 'https://'.$url;
        }

        return $url;
    }
}
