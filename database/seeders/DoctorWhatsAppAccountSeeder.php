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
                'access_token' => 'EABBbX1ZCpBBsBSG8BIdlikL1TZC8nWjJlZAXZAZAkungKvp2zT5JMx6ZC0iZAzw8lNu3WrXcbWs5ZBaNZCcNY0UxTQmdeZBDHeZAhXL23XQaDTmZCPbB04hZCeEV0shWiFGnSh28Nj5INPDJHx3Ir27gBvIjxhe13FVdzJBzAiEBg2lG3bZAXc2hwhrU9lp2LEiFG7z1IkDLu3APAUX7tEwtUAh70BckzYNoIQah19lrqSgNxICCDrFR4NPMZCcmuMLuOZAU4D19KyV0duKN7H2YsZAwm8Yw6IgBi0QZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}