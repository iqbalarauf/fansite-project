<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (!AppSetting::query()->exists()) {
            AppSetting::create([
                'app_name' => 'Fansite Project',
                'sidebar_name' => 'Fansite',
                'hero_path' => null,
                'login_image_path' => null,
                'app_logo_path' => null,
            ]);
        }
    }
}
