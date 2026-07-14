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
                'access_token' => 'EABBbX1ZCpBBsBR86ZAb0NbIdgb34WOJDEjVmNYs1z8elrbyyl209n47vrYMSkTgICBNYnsx6VD6dLaWZACmtJG39mk53YwYu9EFbRgbeeXoiXgZChsN32tyA2G5IMnQMB8vh7VjD3E1pe4qGXCwPBF1WvLONnVS6RS3rOA5o4On1p1UWyfwmVXmcqgfCRo0ZBvyUy8PVCEsAZAZB0Qg7DxOtL2vzp8WZBqCGwfKyFfWHsaQZApiuHaZCOpS2HO5w97iugGT7fgX5tqc20wTZC4MfF1Bsm44',
                'is_active' => true,
                //commit
            ]
        );
    }
}