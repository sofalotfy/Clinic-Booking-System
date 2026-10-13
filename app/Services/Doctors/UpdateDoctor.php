<?php

namespace App\Services\Doctors;

use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;

class UpdateDoctor
{
    public static function execute(Doctor $doctor, array $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $data = array_filter($data, function ($value) {
            return !is_null($value);
        });

        $userData = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'age', 'gender', 'area', 'image', 'password']));
        if (!empty($userData)) {
            $doctor->user->fill($userData);
            $doctor->user->save();
        }

        if (isset($data['description'])) {
            $doctor->update([
                'description' => $data['description']
            ]);
        }

        $doctor->load('user'); // Refresh the user relationship

        return ShowDoctor::execute($doctor);
    }
}
