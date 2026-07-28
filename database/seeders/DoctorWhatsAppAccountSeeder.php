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
                'access_token' => 'EABBbX1ZCpBBsBSIvt8SRdHfZBFiSAQ9mIwliIyirvuZB6ZAULa87xYwxz5MKuClwqLQGVqYLNeSKwnBaxaKRF3XI04KwOouI8oZAUuXKFmEb49ZCoPLlaj55MZBJDfXJsHrUXZBXaEB0STXxxki56FZCCK1eDWj4mARGwklDioqQABoG1Sh2Eg9XeZABZCKCKfPnh43Pb5vpBHgj6Ma1WcMoCgekY1co3Cc4HHB8dobPNomM9im3H9pI6XfWmz85pRZANqQBqqZBUGLsglUYk3L94xWH7lCYa',
                'is_active' => true,
                //commit
            ]
        );
    }
}