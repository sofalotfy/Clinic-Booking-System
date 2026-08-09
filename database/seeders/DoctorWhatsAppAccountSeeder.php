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
                'access_token' => 'EABBbX1ZCpBBsBSEZBZAQmx39LAojHln4xDysDnzLsSrZAAqice1Fk6LbyUBAZCyVGvVZA6NFJt6b9bfT8ocpEsWue0NBfVNZCxz46PEXKtsNcZCId952PPq5QZC8NjbmPgbmVKt2ySZBqKPNGSZCH370AIhfxfx1T5YzwzbTiWZC4uGlNlAxpW1G7dqdmxzA5C98gmMC7dmrz2mPZBtp1SKZAWqhDYA4347vgtdZBkgXrVm8Wy7DAUastoVYmZCqcGFCsdAP6wUMHRuaacxAplHnsWp7bWQmiO9kmAZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}