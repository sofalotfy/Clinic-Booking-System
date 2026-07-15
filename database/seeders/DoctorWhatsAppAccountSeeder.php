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
                'access_token' => 'EABBbX1ZCpBBsBR8R8XksNTlaod1mOBro9kWKX0ZCpWhy33nAeat3p2uF9xZChJCI88EQXFZAjrIt7YeyfrrmXZBOc2UVv4sXNW1XqjDiVf9DCObAFqg7WsoQzuKQZBTRZCewkwkQhAR8snQa3OSSDXbdoOYH92TPUEHHYeWHTYvJXAVvHHc5mIZC0xBG44CshcWZAfxZCsgvbefLcM53Y9pZCdAwZAuvG5ZBE9bEAVjcOOc5iHLJhWYqrvZBLZCNKehdAyWh8ySD6hzR7F4j2evyC4P0Glr0MBnMQZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}