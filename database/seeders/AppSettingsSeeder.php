<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Gambar (app_logo, hero_image, login_image, youtube_playlist_url) dikosongkan.
        $settings = [
            'brand_color' => '#6C7CE8',
            'brand_color_secondary' => '#A5B4FC',
            'brand_color_tertiary' => '#FFD166',
            'app_name' => 'FANSIGHT DEV',
            'sidebar_name' => 'FANSIGHT DEV',
            'desc_app' => 'Welcome to FANSIGHT - your ultimate destination for all things related to your favorite idol!',
            'app_logo' => null,
            'hero_image' => null,
            'login_image' => null,
            'hero_button_1_enabled' => 'true',
            'hero_button_1_label' => 'Lihat Profil',
            'hero_button_1_link_type' => 'url',
            'hero_button_1_link_value' => '#about',
            'hero_button_2_enabled' => 'true',
            'hero_button_2_label' => 'Jadwal Terbaru',
            'hero_button_2_link_type' => 'url',
            'hero_button_2_link_value' => '#schedule',
            'youtube_embed_enabled' => 'false',
            'youtube_playlist_url' => null,
            'youtube_display_mode' => 'cards',
            'news_enabled' => 'true',
            'blog_enabled' => 'true',
            'magazines_enabled' => 'true',
            'trivia_enabled' => 'true',
            'photobooth_enabled' => 'true',
            'sheet_integration_enabled' => 'false',
            'gallery_mode' => 'photos',
            'welcome_feed_source' => 'news',
            'header_menu_mode' => 'default',
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

        DB::table('app_settings')->upsert($rows, ['key'], ['value', 'updated_at']);
    }
}
