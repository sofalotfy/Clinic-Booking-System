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
                'access_token' => 'EABBbX1ZCpBBsBRzAgDGXiCBZAZBuJEVNCgatoMuPZBfgRakcV8hgERY9VZB7wruwZA9IHizo0ZCZAsdqidgSUCybD9r8Bhv9SMCDWtBad4bJRrMPaAl82KeOMYzKR2ZC6BGbIcFLpYl1sgldWwg34ZBzZCZCBY3D4YYUUIRnljiG9770NOiMoO7us3p1VsovZC9wlZBmfDlPurqUOIuul66iaFJutATojovOXO9PegxjkSfb6n0gvl3LJoQQGUMLDO1zgqQZBeowbbLozSBSfvcuxbsbRuWwLSuBgZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}