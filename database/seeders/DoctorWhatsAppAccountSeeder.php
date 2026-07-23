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
                'access_token' => 'EABBbX1ZCpBBsBSDApGjZAYvYGUiHLGYKoX7ZBjujeOXDgvZA9STgS85qWMxZBRskiZCHW1buxb0TxTlB2jgoEfcZA4ys8AQoRmDZBfmOdUSLft8liBiFVVibZB5jc1Wh2gJBBjnjyi485r3bQUom9rZBiYDrRBq82gAVNsTpQOC5szZB7Ka1c6Xv0ck7XzYZCZCyuZBSHYEfm1vaM19xwkRZC0WnzMvu9YZCN6EZAbMwZBLY1ZAQ5AWuehmjZAKTaoC3eZAWGZAuds2Xt4fM3UfY5GmVUVoaA3TGQMLKhB2gZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}