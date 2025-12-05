<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShowTeaterCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories
        DB::table('show_teater_categories')->truncate();

        // Step 1: Insert all unique setlists first
        $setlists = DB::table('show_teater')
            ->select('setlist')
            ->distinct()
            ->whereNotNull('setlist')
            ->where('setlist', '!=', '')
            ->orderBy('setlist')
            ->pluck('setlist');

        $setlistIds = [];
        foreach ($setlists as $setlist) {
            $id = DB::table('show_teater_categories')->insertGetId([
                'type' => 'setlist',
                'name' => $setlist,
                'setlist_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $setlistIds[$setlist] = $id;
        }

        // Step 2: Insert unit songs with their setlist relationships
        $unitSongsData = DB::table('show_teater')
            ->select('setlist', 'unit_song')
            ->distinct()
            ->whereNotNull('unit_song')
            ->where('unit_song', '!=', '')
            ->whereNotNull('setlist')
            ->where('setlist', '!=', '')
            ->orderBy('setlist')
            ->orderBy('unit_song')
            ->get();

        foreach ($unitSongsData as $data) {
            if (isset($setlistIds[$data->setlist])) {
                DB::table('show_teater_categories')->insert([
                    'type' => 'unit_song',
                    'name' => $data->unit_song,
                    'setlist_id' => $setlistIds[$data->setlist],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
