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
                'access_token' => 'EABBbX1ZCpBBsBR5r2AaQFIUvAUKyw6ZBVZBztPljRju90MLuUVYtSHzWbRDxBFuPVffzCslSuxNDZALNBFuc22kmZAD3dfRoSaKsUQtPA6fFESoVPbqsksxTO4kJ2uHg1bdokF9PKzfSNfrjgmFB0mUnjTUkuTIf3nPZAZAykCNsEiaomgxVrSmytUdgVaZBsXPPOHR2luKveC7zn14qcxageX7Eg4ADYeFeEshTI7klKHZA3OEhUyQTZCcSKltDt7eWdlX5r6DDZAJ26HdKu41IZBZBIDoUj',
                'is_active' => true,
                //commit
            ]
        );
    }
}