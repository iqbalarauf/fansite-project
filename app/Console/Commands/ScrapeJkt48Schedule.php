<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
    protected $description = 'Scrape JKT48 theater schedule for Cornelia Vanisa performances';

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
            foreach ($data as $performance) {
                $this->line("- Show: {$performance['show']}");
                $this->line("  Setlist: {$performance['setlist']}");
                $this->line('');
            }
        } elseif (empty($data)) {
            $this->info('No performances found for Cornelia Vanisa.');
        } else {
            $this->error('Failed to parse scraper output.');
            $this->line('Output: ' . $output);
        }

        $this->info('Scraping completed.');
    }
}
