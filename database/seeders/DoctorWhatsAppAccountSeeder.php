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
                'phone_number_id' => '1182572521616442',
                'access_token' => 'EAAOgEL1mYvEBSPPSZCSxYnZAYKYazWxeTV0mUo0YMH3dVAadZCunDCz2i940fQzPueZAxYJXobT86g8fCZBQX02Ix5WEoZCuOmjHn0DQ580fvhe0p6XaKhv2xBgcBbvkEGxJdBKbODNSyP2ZB5YbV60UcVSC3kS3UZBc5kF7bGkVMktJvf4ZB4SnSi2hXFhKUkEKPMQZDZD',
                'is_active' => true,
            ]
        );
    }
}