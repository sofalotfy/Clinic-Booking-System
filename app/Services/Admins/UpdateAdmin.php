<?php

namespace App\Services\Admins;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateAdmin
{
    public static function execute(User $admin, array $data)
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $data = array_filter($data, function ($value) {
            return ! is_null($value);
        });

        $userData = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'age', 'gender', 'area', 'image', 'password']));

        if (! empty($userData)) {
            $admin->fill($userData);
            $admin->save();
        }

        return ShowAdmin::execute($admin);
    }
}
