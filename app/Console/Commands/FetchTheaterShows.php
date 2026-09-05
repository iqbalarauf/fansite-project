<?php

namespace App\Console\Commands;

use App\Models\AboutSettings;
use App\Models\ShowTeater;
use App\Models\TheaterReference;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

class FetchTheaterShows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-theater-shows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch theater shows data from JKT48 API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Hapus reference_code dari bulan sebelumnya
        TheaterReference::deleteOldReferences($currentMonth, $currentYear);

        // Ambil idol_name dari about_settings
        $idolName = AboutSettings::where('key', 'idol_name')->value('value');
        if (! $idolName) {
            $this->error('Idol name not found in about_settings.');

            return self::FAILURE;
        }

        // Ambil data dari API schedules
        $schedules = $this->fetchFromApi("https://jkt48.com/api/v1/schedules?lang=id&month={$currentMonth}&year={$currentYear}&type=SHOW");
        if (! $schedules) {
            $this->error('Failed to fetch schedules from API.');

            return self::FAILURE;
        }

        // Simpan atau perbarui semua reference_code dari jadwal saat ini
        foreach (collect($schedules['data'] ?? $schedules) as $schedule) {
            $referenceCode = $schedule['reference_code'] ?? null;
            if (! $referenceCode) {
                continue;
            }

            TheaterReference::firstOrCreate([
                'reference_code' => $referenceCode,
            ], [
                'month' => $currentMonth,
                'year' => $currentYear,
            ]);
        }

        // Proses semua reference yang belum diproses untuk bulan ini
        $references = TheaterReference::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->whereNull('processed_at')
            ->pluck('reference_code');

        foreach ($references as $referenceCode) {
            $this->processReference($referenceCode, $idolName);
            usleep(300000); // Jeda 300ms antar request
        }

        $this->info('Fetch completed.');

        return self::SUCCESS;
    }

    private function processReference(string $referenceCode, string $idolName): void
    {
        $details = $this->fetchFromApi("https://jkt48.com/api/v1/theater-shows/{$referenceCode}?lang=id");
        if (! $details) {
            $this->error("Failed to fetch details for reference_code: {$referenceCode}");

            return;
        }

        $data = $details['data'] ?? null;
        if (! $data) {
            $this->error("No detail data for reference_code: {$referenceCode}");

            return;
        }

        $memberNames = array_column($data['jkt48_member'] ?? [], 'name');
        $matched = in_array(trim($idolName), array_map('trim', $memberNames), true);

        if ($matched) {
            $dateSlash = Carbon::parse($data['date'])->timezone('Asia/Jakarta')->format('Y/m/d');
            $dateDash = Carbon::parse($data['date'])->timezone('Asia/Jakarta')->format('Y-m-d');
            $title = trim($data['title']);

            $existing = ShowTeater::where(function ($query) use ($dateSlash, $dateDash) {
                $query->where('show_date', $dateSlash)
                    ->orWhere('show_date', $dateDash);
            })
                ->where('setlist', $title)
                ->first();

            if ($existing) {
                $this->info("Show already exists: {$existing->show_id} - {$existing->show_date} - {$existing->setlist} (skipped)");
            } else {
                $lastShowId = ShowTeater::max('show_id') ?? 0;
                $newShowId = $lastShowId + 1;

                ShowTeater::create([
                    'show_id' => $newShowId,
                    'show_date' => $dateSlash,
                    'setlist' => $title,
                    'is_scraped_data' => 1,
                ]);

                $this->info("Saved show: {$newShowId} - {$dateSlash} - {$title}");
            }
        }

        if (! empty($data['jkt48_member'])) {
            TheaterReference::where('reference_code', $referenceCode)
                ->update(['processed_at' => now()]);
        }
    }

    private function fetchFromApi(string $url, int $retry = 2): ?array
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
            'Accept' => 'application/json, text/plain, */*',
            'Referer' => 'https://jkt48.com/schedule',
        ];

        for ($attempt = 1; $attempt <= $retry; $attempt++) {
            $response = Http::withHeaders($headers)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            // Fallback to system curl (bypasses Cloudflare TLS fingerprinting on Windows)
            $process = Process::run([
                'curl.exe',
                '-s',
                '-H', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
                '-H', 'Accept: application/json, text/plain, */*',
                '-H', 'Referer: https://jkt48.com/schedule',
                $url,
            ]);

            if ($process->successful()) {
                $data = json_decode($process->output(), true);
                if (is_array($data) && ! empty($data)) {
                    return $data;
                }
            }

            if ($attempt < $retry) {
                usleep(500000); // 500ms delay sebelum retry
            }
        }

        return null;
    }
}
