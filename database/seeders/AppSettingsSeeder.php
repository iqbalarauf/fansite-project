<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_name',
                'value' => 'FANSIGHT DEV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sidebar_name',
                'value' => 'FANSIGHT DEV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'desc_app',
                'value' => 'Welcome to FANSIGHT - your ultimate destination for all things related to your favorite idol!',
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
        ];

        // Use bulk insert for better performance
        DB::table('app_settings')->upsert(
            $settings,
            ['key'], // unique column
            ['value', 'updated_at'] // columns to update
        );
    }
}
