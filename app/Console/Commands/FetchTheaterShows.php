<?php

namespace App\Console\Commands;

use App\Models\AboutSetting;
use App\Models\ShowTeater;
use App\Models\TheaterReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

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
        $idolName = AboutSetting::get('idol_name');
        if (!$idolName) {
            $this->error('Idol name not found in about_settings.');
            return;
        }

        // Ambil data dari API schedules
        $response = Http::get("https://jkt48.com/api/v1/schedules?lang=id&month={$currentMonth}&year={$currentYear}&type=SHOW");
        if (!$response->successful()) {
            $this->error('Failed to fetch schedules from API.');
            return;
        }

        $schedules = $response->json();
        // Asumsikan struktur API, misalnya array of objects with reference_code
        // Saya perlu asumsikan struktur, mungkin 'data' array dengan 'reference_code'

        // Simpan atau perbarui semua reference_code dari jadwal saat ini
        foreach (collect($schedules['data'] ?? $schedules) as $schedule) {
            $referenceCode = $schedule['reference_code'] ?? null;
            if (!$referenceCode) {
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
        }

        $this->info('Fetch completed.');
    }

    private function processReference(string $referenceCode, string $idolName): void
    {
        $detailResponse = Http::get("https://jkt48.com/api/v1/theater-shows/{$referenceCode}?lang=id");
        if (!$detailResponse->successful()) {
            $this->error("Failed to fetch details for reference_code: {$referenceCode}");
            return;
        }

        $details = $detailResponse->json();
        $data = $details['data'] ?? null;
        if (!$data) {
            $this->error("No detail data for reference_code: {$referenceCode}");
            return;
        }

        $memberNames = array_column($data['jkt48_member'] ?? [], 'name');
        $matched = in_array($idolName, $memberNames, true);

        if ($matched) {
            $existing = ShowTeater::where('show_date', $data['date'])
                ->where('setlist', $data['title'])
                ->first();

            if (!$existing) {
                $lastShowId = ShowTeater::max('show_id') ?? 0;
                $newShowId = $lastShowId + 1;
                $showDate = Carbon::parse($data['date'])->timezone('Asia/Jakarta')->format('Y-m-d');

                ShowTeater::create([
                    'show_id' => $newShowId,
                    'show_date' => $showDate,
                    'setlist' => $data['title'],
                    'is_scraped_data' => 1,
                ]);

                $this->info("Saved show: {$newShowId} - {$data['date']} - {$data['title']}");
            }
        }

        if (!empty($data['jkt48_member'])) {
            TheaterReference::where('reference_code', $referenceCode)
                ->update(['processed_at' => now()]);
        }
    }
}
