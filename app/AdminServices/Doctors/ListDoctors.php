<?php

namespace App\AdminServices\Doctors;

use App\Services\Doctors\ListDoctors as ListDoctorsService;

class ListDoctors
{
    public static function execute($request)
    {
        return ListDoctorsService::execute()
            ->paginate(15)
            ->through(fn ($doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->user?->name,
                'email' => $doctor->user?->email,
                'phone' => $doctor->user?->phone,
                'gender' => $doctor->user?->gender?->value,
                'area' => $doctor->user?->area,
                'image' => $doctor->user?->image,
                'clinic_name' => $doctor->clinic?->name,
                'whatsapp_active' => $doctor->whatsappAccount?->is_active,
                'joined' => $doctor->user?->created_at?->format('Y-m-d'),
            ]);
    }
}
