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
                'access_token' => 'EABBbX1ZCpBBsBRyUZAEPnj7V9DwibODzLV9BsG9PYZBi1PPCoPxEKZBWcSABbAfHsfOiRK8sTECGMBtBspZASZBPSZC4VY2wuzKPomPbsnZBSLFNVuZApVEUJKvJKjjLQlvj9ciZCVvixfaPCabDvxj7faCi4UgVXhrF8oZAgFzE0xBA1WzxxAPufosireX8E1k6oHSxbOufGur9ZCNcsQ7BqM1Jx5QveJ6lxVy2PE8kQD9KG7r451QSp6zsw5MBGvqkZA3yEX7HcYt1mvH8qkkG3wbvHrsG8',
                'is_active' => true,
                //commit
            ]
        );
    }
}