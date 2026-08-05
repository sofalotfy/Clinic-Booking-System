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
                'access_token' => 'EABBbX1ZCpBBsBSB5i3Gd1isTA1lITd3jr2C1gQlZCTCwiYIpNCE48rJDpvHH6i1ZA3fBUljQWi8kwjNcC6rClyern1EdZBwn9E516xPjetdBCGKM0tmBmjvPShdZCOtEYQ8iXd8GdUoHKW2lOXG8rPydyQYW4GrM4OdnOj8vJ6TB1KZCH3yQyGzz6FiueMkU7Oibqm1YwcNTlZCZA0Ib2lKpA8zSFRedJtxd5Cbh6DQ1koUWy7Q0WZC8hW63gZB9nvQ4DVjAQ6sPnm9wEChXQG8T0mJecWwscZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}