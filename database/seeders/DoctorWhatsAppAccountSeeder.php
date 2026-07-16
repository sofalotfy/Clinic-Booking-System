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
                'access_token' => 'EABBbX1ZCpBBsBR8olRmpXkLUWoXkrojXZCM1ZBhWhO6nBHNwwyPwASn7a9jonMgSM9jW30EFrD2SpuKdbMZBZA2ZBn5mRitKfjcANHFhJsOdJjFLZB61q1alD4QEGt3shXog2ZA5ZCD3nPJSf4M0EkO3yH48Wd3JbtyjrytWAyUtwdtff9Vuw30iTTpjhWPIoUWxq2xtgBf27q2WGixCpLoV0IXMGWyFVsg5Gw7CtN15BTN0wZCGDiWSJMjNJsk34plgGNv3NouCcVNakV8VhnnqCQonzzuQZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}