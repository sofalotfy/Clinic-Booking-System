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
                'access_token' => 'EABBbX1ZCpBBsBSC1npZCZC9tVdmjzFysTYB9qBjNeGNp0N6ur738ci4Fw6o2mVG5Ghxb3GBjtaCy2WqECO16G5tRwEANoa8ZCZApqK66dqo4YuuglNxbI0pgKeZBg846vGoPMw1cNSXnyVYTLu2UaXDNC8r1AdsBsM3aiEfkFBaSVhzabWvTDeKpSWYjZAaoTN4zRZCG2cqUZBFgr7keziXCZCG65OZCVZCWk5mPGJXeJQJ9CxHUlpiEVdgQycpiJCMNXG5LSQlKCIoTXRjmsnoEAPzwtH88',
                'is_active' => true,
                //commit
            ]
        );
    }
}