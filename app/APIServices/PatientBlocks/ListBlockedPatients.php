<?php

namespace App\APIServices\PatientBlocks;

use App\Models\User;
use App\Models\PatientBlock;
use App\Enums\UserType;
use Illuminate\Support\Facades\DB;

class ListBlockedPatients
{
    public static function execute()
    {
        $user = auth()->user();

        if ($user->type !== UserType::DOCTOR) {
            return null;
        }

        $query = PatientBlock::query()
            ->join('patients', 'patient_blocks.patient_id', '=', 'patients.id')
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->where('patient_blocks.doctor_id', $user->doctor->id)
            ->whereNull('patient_blocks.unblocked_at')
            ->where(function ($query) {
                $query->whereNull('patient_blocks.expires_at')
                    ->orWhere('patient_blocks.expires_at', '>', now());
            });


        // if ($search) {
        //     $query->where(function ($query) use ($search) {
        //         $query->where('users.name', 'like', "%{$search}%")
        //             ->orWhere('users.phone', 'like', "%{$search}%")
        //             ->orWhere('users.area', 'like', "%{$search}%");
        //     });
        // }


        return $query
            ->select(self::getSelects())
            ->get();
    }


    private static function getSelects(): array
    {
        return [
            'patients.id as patient_id',

            'users.name',
            'users.phone',
            'users.email',
            'users.image as avatar',
            'users.age',
            'users.area',

            'patient_blocks.id as block_id',
            'patient_blocks.reason',
            'patient_blocks.blocked_at',
            'patient_blocks.expires_at',
        ];
    }
}