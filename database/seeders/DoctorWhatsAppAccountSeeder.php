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
                'access_token' => 'EABBbX1ZCpBBsBSBvoLBtqYcRDZBvMBYTp56gZCt6ZCRBwYEi2z0EF0u6AuKRx5OOFYCuygpXOzs4rXA0ZCeufV2xjHbDKDzsMEwULEwO8WtdAKO8lgTPhuzoOvObF8Ht8quhM1oK6Bb1WQ0p7E24jlSTgzpP4IxLxDJbdB4hZBxtIVoqD61wk4Am0As8qeZA2A9Mx7ZCz5EG07fxS7kPYi0eUhKgCsuDnIUE4C7ZCZAl2bHIfjl42O7sakRHz9JzSscZACPRTzrz4RAuHtZCSum33BMeZBBkb',
                'is_active' => true,
                //commit
            ]
        );
    }
}