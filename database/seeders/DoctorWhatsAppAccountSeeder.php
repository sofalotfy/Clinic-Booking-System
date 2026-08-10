<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DoctorWhatsAppAccount;

class DoctorWhatsAppAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DoctorWhatsAppAccount::updateOrCreate(
            ['doctor_id' => 1],
            [
                'phone_number_id' => '1137805152755860',
                'access_token' => 'EABBbX1ZCpBBsBSHjYua9Ap5y3y5HCpYDMZCJiLN7ebQrpLgZA8XLFyfZAPlZBZA0NfAfy6fYDSwawfAbZCq6ZBkrpgym867yomBKZA2eEYolj1m0MzFQ5RQZA3b1hF4OdZCnbkofAHvo3qQlFf2qDhqTFSDyZAh6ZA72IIS6MXc83FFwIZB56ZCqL6lpfTujOrDdubzibeIOOZADPgF45azohEFVUh7oboIQckpHV0D1Hc7bi2rhZBBjbQ0HDTTRhQj2Me6G79S6zYZARGy7EpYXqNFOTcalXNRTTb',
                'is_active' => true,
                //commit
            ]
        );
    }
}