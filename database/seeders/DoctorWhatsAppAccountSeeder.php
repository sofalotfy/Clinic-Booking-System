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
                'access_token' => 'EABBbX1ZCpBBsBSL8uJ980hVu9Hl5F2oOEvow6NYlyhJxXZArk0228ZANwPwsn2oMAL4mJooILHuIHUVI1xfu1OQjloUZAZCt4oaJug605FazRcwjskwJopZAYJc1YnHtW1FDZBlmZCy1FRko5Rx9ioR0dHZBiSEDJbyMLj4aXLlZAgM9S8IAyk91giBebnT2aXEHuu0mlmFlgpgvTthIvNEvPClT75cYB2kLDFodl1nIAfJLn6iZBeuN258P2ZB6ryMpRktO73VyPsCcCRQCLSt2m3p7HD6ZA',
                'is_active' => true,
                //commit
            ]
        );
    }
}