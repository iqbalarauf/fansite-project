<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AboutSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // About Idol Settings
            [
                'key' => 'idol_name',
                'value' => 'Your Idol Name',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idol_slug',
                'value' => 'your-idol-name',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idol_photo',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idol_description',
                'value' => 'Welcome! This is a brief introduction about your favorite idol.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idol_achievements',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idol_discography',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idol_social_media_twitter',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idol_social_media_tiktok',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idol_social_media_instagram',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idol_show_on_welcome',
                'value' => 'false',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // About Fanbase Settings
            [
                'key' => 'fanbase_name',
                'value' => 'Fanbase Community',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_slug',
                'value' => 'fanbase-community',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_logo',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_description',
                'value' => 'Join our vibrant community of dedicated fans!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_activities',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_gallery',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_cta_enabled',
                'value' => 'false',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_cta_title',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_cta_description',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_cta_button1_text',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_cta_button1_link',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_cta_button2_text',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_cta_button2_link',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fanbase_cta_background',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('about_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
