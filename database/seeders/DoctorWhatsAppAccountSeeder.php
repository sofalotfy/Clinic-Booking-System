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
                'access_token' => 'EABBbX1ZCpBBsBSHuDC1j8WXAvRdjY9tN4Y9woHaEtyvlvmgO6aQlzXR0jCZAJJUxyVqPov2B4FeT9i1h7i6ZAHDZC8CMCDyjpuGLuZADp1LL0xfLj3jlsBXl1wNwMc8b5gGrtiL2NBSOlZCHdXJjseu2gwv2FBmIoj1xW4qGTvsgZCiKHx9R9qSVyINcndZBDY2U5VVVrMjw5COx1dRGCByTjLJ9FRUinWnTT97egeSD2pr6PLxjIJnMxoKvUlzzTP9H3tWjGHWBBfN8r7rqreqlHzZCZB',
                'is_active' => true,
                //commit
            ]
        );
    }
}