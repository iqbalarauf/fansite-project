<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShowTeater;

class ScrapeJkt48Schedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-jkt48-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape JKT48 theater schedule for Cornelia Vanisa performances and save to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting JKT48 schedule scraper...');

        $output = shell_exec('node scraper.js 2>&1');

        // The output should be JSON
        $data = json_decode(trim($output), true);

        if (json_last_error() === JSON_ERROR_NONE && !empty($data)) {
            $this->info('Found performances for Cornelia Vanisa:');

            // Sort data by date
            usort($data, function($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            // Clear existing data
            ShowTeater::truncate();

            // Save to database with sequential show_id
            $showId = 1;
            foreach ($data as $performance) {
                ShowTeater::create([
                    'show_id' => $showId,
                    'show_date' => $performance['date'],
                    'setlist' => $performance['setlist'],
                    'unit_song' => '', // Leave empty for now
                    'is_global_center' => null,
                    'is_us_center' => null,
                    'is_the_show_has_event' => null,
                    'additional_information' => null
                ]);

                $this->line("  Show: {$performance['date']}");
                $this->line("  Setlist: {$performance['setlist']}");
                $this->line("  Saved with show_id: {$showId}");
                $this->line('');

                $showId++;
            }

            $this->info('Data saved to database successfully.');
        } elseif (empty($data)) {
            $this->info('No performances found for Cornelia Vanisa.');
        } else {
            $this->error('Failed to parse scraper output.');
            $this->line('Output: ' . $output);
        }

        $this->info('Scraping completed.');
    }
}
