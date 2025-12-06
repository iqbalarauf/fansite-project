<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_name',
                'value' => 'Fansite Project',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sidebar_name',
                'value' => 'Fansite',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'desc_app',
                'value' => 'Welcome to our fansite - your ultimate destination for all things related to your favorite idol!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'app_logo',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'hero_image',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'login_image',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'showroom_room_id',
                'value' => '416491',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'showroom_link',
                'value' => 'https://www.showroom-live.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'instagram_url',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'twitter_url',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tiktok_url',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
