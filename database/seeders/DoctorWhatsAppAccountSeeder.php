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
                'access_token' => 'EABBbX1ZCpBBsBSKF07AAZBOn2b77BzyiDAPIgr7PBxQc5rwqtii3xNdYTJcObhNylJ5Tkj1qa2L1SSiRgSDLBV9lTDdOIA6jWnstsisTTWHi12zMya5w3nGAYBE38WhqVQf650A0g7G7VBmXGKwiyQk5xKm1rWwv3z5VZAGh6snZBj5Df608YGoZCqZASmw0FVCpVW6pS6Q5K8JiE1xEJrq08I9buP0qhu4sa188BOOawKHCVomRFQPykVn2phr96oylEpVLAzSE99LV3XwPnpcAVTxAZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}