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
                'access_token' => 'EABBbX1ZCpBBsBR5IQTTUN1ajDPtF2egY9sCrZBTtLCli8dNeezP4ygZAlR5GylhVZCZBbQBl5zdFl1EwIZC9GZBNytpmMnkbSqjvEonfHoveUcsAg3L3H7oD8cdElS3sLkeC4nYAYAkXZCQl6TCywSXXPD6ZCZBAscofgv56DeX1ZBSRKrZAugwB06TyZCT0OZCSGvKIyZAIZBwAwZBtztrSL7FHSb6rqyxqZB7ZBb0N2fFfFB8VAOeYSZBvS6B80ZBjWvZAyvfyIDPijiMpN4GN4tTBg41M2Avt7J52DH',
                'is_active' => true,
            ]
        );
    }
}