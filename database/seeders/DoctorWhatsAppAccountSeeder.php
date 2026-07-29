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
                'access_token' => 'EABBbX1ZCpBBsBSCrBA9kEMNCRX3oqljLWhgiMtKkRsNceoX0KHY0itrzWVBlilLXBUWviuobyH2GlnFaMsWyrjci9bX7qVEeoZCRNtk8dRanCKs3Fy2h4QEJcZAgSdpWhx6S0G2FlnU7ZA9laAbXOZAgVSx0DlWMZAfcAYOWZCW51geHlkMA5foYATwaDKhbFr2ffSsHiy9JIZC6WUAzhZBhZCojqPO7li7PeECzrbspa9jm3lfBZAToSJ9NL7C7gnUtt0ZBNQrqd9REZBooZAwu960zcXIPcE',
                'is_active' => true,
                //commit
            ]
        );
    }
}