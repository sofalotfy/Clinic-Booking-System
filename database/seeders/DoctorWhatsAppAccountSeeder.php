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
                'access_token' => 'EABBbX1ZCpBBsBRwZCZCkFFBS3uWVIG27aoLZBuq9HZAXj2vFCLjTHPzK1zl061b6gT1EEn7FsNqItdqyeEZBjloTgiZAqWjazDOPryNURXUNsnoNNDZAyUqBVUitABleTXzcGZCqIf8xfeehVvF1Cc3jrt9Tyj3dKVDZCamPDeV8sFYLNzEJzlV4AXAfVm4YQ8kJTf9tjYtLZAa0HAMBEzNuZBV4HSRVXvcKiSuZCVXBMLWS59AepfZAHrwTgJUEaSC95ozObbPuA7PnZCl5OVO48PECI3gNWhB8gZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}