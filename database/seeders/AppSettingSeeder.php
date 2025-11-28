<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('app_name', 'Fansite Project');
        Setting::set('sidebar_name', 'Fansite');
        Setting::set('desc_app', 'Sebuah antologi yang menunagkan segenap dukungan dalam perjalanan Cornelia Vanisa.');
        Setting::set('app_logo', '/storage/logo.png');
        Setting::set('hero_image', '/storage/hero.png');
        Setting::set('login_image', '/storage/login.jpg');
    }
}
