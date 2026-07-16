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
                'access_token' => 'EABBbX1ZCpBBsBRZC3QQgDgj91YtqpsevgNhZBxOE2vxdWEYENm90WZCseVvzw7K1qmZAgVkMZBL7xSZAu5dnO8JA5rN08VO6QvIinZBySJVWS1SZAdsxEQ4VajthUnfbnxoWqvm46yw3ofIx2aowftF1ycKN8ijYjVZC8y1lZB2GMCKKBvRPXgSxXYpTyD7ZCm8V8hHYa4ZAHBzrFJXjAD3NYyJuLxMsjwmgw5LsJ0JFW5ZAhrQEhIyenqvvjfqq5daQ6ZCsoRpSUd2jcKCkgyrWZCcx7XryZBiqd',
                'is_active' => true,
                //commit
            ]
        );
    }
}