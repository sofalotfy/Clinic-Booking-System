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
                'access_token' => 'EABBbX1ZCpBBsBSENVZBJtiWUKCgC7XpAWxWWd13I7OM2m3axloZBs3i9KsAuGwGSWjCGJnoSb0Qq00jtnleZAdeBkiZCVcQ1VTw06qq0zutHqAIUe4hIFnAMIR95HkNrw5tVH3dXJWtme1ZCxjwXsATPKJCWSdivtJZCL7x4rTVNjc5VpikHzmvhES0BK7u6MihRMOE8Cm7ZBKT62vbiHMRl2shsncEvv6XN4jQxH0BnVPlyA3H7kn7ZAbzXuaHVkv4NxFKHhfjAb3zjg05rbNsL0Xoc7',
                'is_active' => true,
                //commit
            ]
        );
    }
}