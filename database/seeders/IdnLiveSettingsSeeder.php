<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdnLiveSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'idn_live_username',
                'value' => 'jkt48_oniel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idn_live_check_interval',
                'value' => '30', // seconds
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'idn_live_enabled',
                'value' => '1', // 1 = enabled, 0 = disabled
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
