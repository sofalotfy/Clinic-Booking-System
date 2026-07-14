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
                'access_token' => 'EABBbX1ZCpBBsBR9zQZBDvD1ETjAiKgULbwgQKYoJHRMamQYALJLr5pviqrIXbbBwRa32OZAbIaZCzKeOoreo5ZA4NMoucLnk4DmppVFRu2ZBPH3BQjuxkf12RlVFHalCAuidjLgUEzXBafah5t0tuP3FVMYAk30CQGoL0TbmGudN8YI6x02UVvEiWW0qd0Fg0ZAkiPGIdVsePRLUEitascQxWAOTOtFDGOCA0T6ZANSWT3OTRvKCiZBpdZBwmOCBiloSyUrnMrfwlC0kFRY8ZCG2127ZBA9d',
                'is_active' => true,
                //commit
            ]
        );
    }
}