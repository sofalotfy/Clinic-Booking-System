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
                'access_token' => 'EABBbX1ZCpBBsBSIZBcQLChYGC7jwDP6PTXYhbLoBOPZBDMZAdsvVCNu9dX9qibJw0XjCUXT0InOxqV7Uvz4i2nl6RtZBAejK9UrhnpNymHRlZCzvczNsqFuTuJb9OMZAjwzjZAylutPsuRmZCNq7hez4oEWAWiLb9LdnzLHhPO0MuQr5pexb3aXYo3SRUM5nllfDkud5UetINwEKKbD7SQx7JbX1SnkXtpyZBRe1FjqqNud8X8bz746WpAaF4VswG1Bruw8QIBUoUyS0sZCHVMQCZCOhriRf',
                'is_active' => true,
                //commit
            ]
        );
    }
}