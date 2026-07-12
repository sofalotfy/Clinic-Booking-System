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
                'access_token' => 'EABBbX1ZCpBBsBR48gxwILiGHVe3b5kUHkbll4q62n7O041kJ6JOQneEZCzDwfYuro4OqZACLVViUnadvyfgG4A6OKbIFZCdlSdZCgZCrN5GfBJs7EaELdujoFHFdEgZCsgxmEqXwydAlrZC03FZBBmBE1sjnStxUljnbi1hwhhKqiqbjfAxaHUZCYzirjpEoLKVEM4mFEdCpwaNOAgxt45NcOa0t73WbTc6lDx3TgOB7XBRskLvDTZA5aczvfJ4Al4TovtVJpUv9bok1Uk0ZB15rRCvMqkHMfgZDZD',
                'is_active' => true,
            ]
        );
    }
}