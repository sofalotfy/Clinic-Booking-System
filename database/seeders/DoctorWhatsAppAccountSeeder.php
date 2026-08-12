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
                'access_token' => 'EABBbX1ZCpBBsBSJd2EZA98GkUoZAIeS2b1HZA8BIVxYn1vhGRdQ16d8U99vKCyLJEg8HZBqbS3L13VSXiHV21nmDyzXFfwZBanivuaosbi0AZBtxoivsRf0tQRaFXlhIzj8t5mhvx92sC227EBd6TJ6HBjJNwEhfALMA7bZAqaoQqwy3VfUNQE6ss2yRpcRcRADQCqOO5J8HyFFPNzF7IwAiEOg3ajGZABt3EBKs9sXTtqqwPxBaGqyDJAhw157xqzQjJTULZAZAZBcCoaIObOKruCI5m0x0',
                'is_active' => true,
            ]
        );
    }
}