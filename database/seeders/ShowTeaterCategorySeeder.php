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

        // Step 1: Get all unique setlists
        $setlists = DB::table('show_teater')
            ->select('setlist')
            ->distinct()
            ->whereNotNull('setlist')
            ->where('setlist', '!=', '')
            ->orderBy('setlist')
            ->pluck('setlist');

        // Step 2: Prepare setlist data for bulk insert
        $setlistData = [];
        $now = now();
        foreach ($setlists as $setlist) {
            $setlistData[] = [
                'type' => 'setlist',
                'name' => $setlist,
                'setlist_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert setlists
        if (!empty($setlistData)) {
            DB::table('show_teater_categories')->insert($setlistData);
        }

        // Step 3: Get the inserted setlist IDs
        $setlistIds = DB::table('show_teater_categories')
            ->where('type', 'setlist')
            ->pluck('id', 'name')
            ->toArray();

        // Step 4: Get unique unit songs with their setlists
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

        // Step 5: Prepare unit song data for bulk insert
        $unitSongData = [];
        foreach ($unitSongsData as $data) {
            if (isset($setlistIds[$data->setlist])) {
                $unitSongData[] = [
                    'type' => 'unit_song',
                    'name' => $data->unit_song,
                    'setlist_id' => $setlistIds[$data->setlist],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Bulk insert unit songs
        if (!empty($unitSongData)) {
            // Insert in chunks to avoid memory issues
            $chunks = array_chunk($unitSongData, 100);
            foreach ($chunks as $chunk) {
                DB::table('show_teater_categories')->insert($chunk);
            }
        }
    }
}
