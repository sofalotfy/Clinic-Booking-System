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
                'access_token' => 'EABBbX1ZCpBBsBR7GxBJNB6NoYgIhjtLIkN2ZCl4u2argDIqvd02CxYH7ZCZCly4XzNHPC5EIHQZAWepZBwDMp9gTHZAfHQzvNVJuj68FZAefLLaawX9LwWetM8W64uJRq6ZATiKxZAbDCrksf6Ln5RNnDf93xT8rW6NasYOKZBURPhZBywUID92yW0E1ZAVWNoceofmeYclFt7c25y6M2TIITvv4RAgaMubFNUV0lO0uoXtSDhIUCZCNAQBaIw1sAYxh5ppz5m720vaqIvGxIKBEU21Wf1YXbB',
                'is_active' => true,
                //commit
            ]
        );
    }
}