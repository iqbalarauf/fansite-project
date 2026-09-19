<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Gambar (idol_photo, fanbase_logo, fanbase_cta_background) dan
        // array/JSON (kabesha_items, kabesha_photos, fanbase_gallery_items,
        // fanbase_gallery, fanbase_history_items) dikosongkan.
        $settings = [
            // Idol
            'idol_name' => 'Your Idol Name',
            'idol_shortname' => null,
            'idol_slug' => 'your-idol-name',
            'idol_photo' => null,
            'idol_about' => null,
            'idol_description' => 'Welcome! This is a brief introduction about your favorite idol.',
            'idol_achievements' => null,
            'idol_discography' => null,
            'idol_jikoshoukai' => null,
            'idol_birth_date' => null,
            'idol_birth_place' => null,
            'idol_blood_type' => null,
            'idol_horoscope' => null,
            'idol_social_media_instagram' => null,
            'idol_social_media_tiktok' => null,
            'idol_social_media_twitter' => null,
            'idol_show_on_welcome' => 'false',
            'idol_profile_version' => 'jkt48',
            'instagram_url' => null,
            'twitter_url' => null,
            'tiktok_url' => null,

            // Kabesha
            'kabesha_enabled' => 'true',
            'kabesha_default_title' => null,
            'kabesha_items' => null,
            'kabesha_photos' => null,
            'kabesha_photo' => null,
            'kabesha_title' => null,
            'kabesha_duration_from' => null,
            'kabesha_duration_to' => null,

            // Fanbase
            'fanbase_name' => 'Fanbase Community',
            'fanbase_slug' => 'fanbase-community',
            'fanbase_logo' => null,
            'fanbase_description' => 'Join our vibrant community of dedicated fans!',
            'fanbase_structure' => null,
            'fanbase_structure_enabled' => 'true',
            'fanbase_activities' => null,
            'fanbase_activities_enabled' => 'true',
            'fanbase_gallery_items' => null,
            'fanbase_gallery' => null,
            'fanbase_history_enabled' => 'false',
            'fanbase_history_source' => 'default',
            'fanbase_history_custom_page_id' => null,
            'fanbase_history_items' => null,
            'fanbase_cta_enabled' => 'false',
            'fanbase_cta_background' => null,
            'fanbase_cta_title' => null,
            'fanbase_cta_button1_text' => null,
            'fanbase_cta_button1_link' => null,
            'fanbase_cta_button2_text' => null,
            'fanbase_cta_button2_link' => null,
        ];

        $rows = collect($settings)
            ->map(fn ($value, $key): array => [
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        DB::table('about_settings')->upsert($rows, ['key'], ['value', 'updated_at']);
    }
}
