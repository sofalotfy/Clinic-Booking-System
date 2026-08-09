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
                'access_token' => 'EABBbX1ZCpBBsBSN0phs6bk6pH9aUcbQZBsBixuEiGMujbbsG70qQpwQ2Wf0ktqUcqgltgpO6Amht0AtE9aGqKGXFmtmZCf2YIvCiRlh4Cj1qycVBMBwUs9RRIJ30qRnIkFF9WONkOyfRURjzZAzsm460bfBReCoy8hCe4jgaNqh9POYqTv8cWKXLYHjop8grgogq1mGtm3gtCfKvPiS0oBCaqlwsvCfZAfPH8vkbkOwaKLePOT57bHTXIrK5fNELwRfISbGjaDLJkZCPpv2BPLg8ViZBwZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}