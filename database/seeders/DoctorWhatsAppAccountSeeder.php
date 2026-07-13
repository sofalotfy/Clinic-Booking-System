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
                'access_token' => 'EABBbX1ZCpBBsBRzANTaiVJqjtufhVDvc5WrlsommHcDzzypFNZC8MBsTPBDbJ4HwFDQ1NH4SZAu3M3enQNgZBLKt0HSEL0xHUZB5JUUnQukqKu1smkZCrZAvUenKe91Od6Q0oQkngZBfYCxdx3KzXopjHLwkgJb2mDHki9rG7ZAeZC1cZAftmu3ZAzIOPRc7ZBeqKklNC42E48PYLzqB1XKyHtRnJ1Az2HEiKRH51rBYZAq6wyExGyN9t8hF3VDU5qZAmhZARutxyo46tkwM7e1gKtNScqmKKsgL5QZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}