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
                'access_token' => 'EAASGHkSX5ZA0BSBVyTQ6zMmVGQ0HcFGd6rnNQ9XFnlg9x0reLSu2rqdOTz8oZCdTu4vlfSl49bdSnQfKCW0wlmQQ53KH7PEOYsVXsioXGsxuNzHL0wWIh5etmPHNhCMLCUFMbqv8ZBPH4Xjl4HEWEoMheW88vb7Du8CCRZBVwtYoSTj8mqBZBrJGLArKVlp7aMF1hwoign0sjkSpIbNIOOcLZAxClfYVmM8JiiXu6TjkoRBBPPPf5XN0q5CYkaUkAYOrvUG5kc3bJpZCAa6QHvJS1g5',
                'is_active' => true,
            ]
        );
    }
}