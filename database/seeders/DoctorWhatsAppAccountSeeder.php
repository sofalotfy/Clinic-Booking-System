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
                'access_token' => 'EABBbX1ZCpBBsBR3nMewFVE0rwNZCk9GNKtZAv9Wh81LKOKZApiZBrfSKZCIEKDWy7MZCltBpWe3ZBWSsb2RgyrgzK2Wp2VTYshoQMoszS3epBkxTKBy8JF6wzTBoOac093EM8QPxdgZBewU6gWBWQYExTjYLlJUxMRQhvS16kfZBn99fMjcCgqJLvoRvZCZAvgiKyEwp4oysgz1hL6QGlhM3azyOYOGZC5TT1Wa9zQ0s1GMozZAjVJrymaAvEBHAQOZCzMoZBNXcYVYGIOQCnQprICWc8xEqRNRK',
                'is_active' => true,
                //commit
            ]
        );
    }
}