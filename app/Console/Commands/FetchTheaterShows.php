<?php

namespace App\Console\Commands;

use App\Models\AboutSettings;
use App\Models\ShowTeater;
use App\Models\TheaterReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
    protected $description = 'Fetch theater shows data from JKT48Connect API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseUrl = rtrim((string) config('services.jkt48connect.url'), '/');
        $apiKey = (string) config('services.jkt48connect.key');

        if ($baseUrl === '' || $apiKey === '') {
            $this->error('JKT48Connect API is not configured (JKT48CONNECT_LIVE_URL / JKT48CONNECT_API_KEY).');

            return self::FAILURE;
        }

        $idolShortname = AboutSettings::query()->where('key', 'idol_shortname')->value('value');
        if (! $idolShortname) {
            $this->error('Idol shortname not found in about_settings.');

            return self::FAILURE;
        }

        $payload = $this->fetchFromApi("{$baseUrl}/api/v1/theater?page=1", $apiKey);
        if ($payload === null) {
            $this->error('Failed to fetch theater data from JKT48Connect API.');

            return self::FAILURE;
        }

        $shows = $payload['data'] ?? [];
        if (! is_array($shows) || $shows === []) {
            $this->error('No theater data found.');

            return self::FAILURE;
        }

        foreach ($shows as $show) {
            if (is_array($show)) {
                $this->processShow($show, trim((string) $idolShortname));
            }
        }

        $this->info('Fetch completed.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $show
     */
    private function processShow(array $show, string $idolShortname): void
    {
        $referenceCode = $show['reference_code'] ?? null;
        if (! $referenceCode) {
            return;
        }

        $date = $show['date'] ?? null;
        $title = isset($show['title']) ? trim((string) $show['title']) : '';

        $alreadySynced = $date && $title !== '' && $this->showExists((string) $date, $title);

        // Reference sudah pernah diproses: bila show-nya juga sudah ada, beri tahu sudah tersinkronisasi.
        if (TheaterReference::query()->where('reference_code', $referenceCode)->exists()) {
            if ($alreadySynced) {
                $this->error('Show terbaru sudah tersinkronisasi');
            }

            return;
        }

        if (! $this->lineupIncludesIdol($show['lineup'] ?? [], $idolShortname)) {
            return;
        }

        if (($show['type'] ?? null) !== 'SHOW') {
            return;
        }

        if (! $date || $title === '') {
            return;
        }

        $this->recordReference((string) $referenceCode);

        if ($alreadySynced) {
            $this->error('Show terbaru sudah tersinkronisasi');

            return;
        }

        // Hitung dari seluruh baris (termasuk yang ter-soft delete) agar show_id tidak menimpa PK.
        $newShowId = (int) (ShowTeater::withTrashed()->max('show_id') ?? 0) + 1;

        ShowTeater::query()->create([
            'show_id' => $newShowId,
            'show_date' => (string) $date,
            'setlist' => $title,
            'is_scraped_data' => 1,
        ]);

        $this->info("Saved show: {$newShowId} - {$date} - {$title}");
    }

    private function showExists(string $date, string $title): bool
    {
        $dateSlash = str_replace('-', '/', $date);

        return ShowTeater::withTrashed()
            ->where(function ($query) use ($date, $dateSlash): void {
                $query->where('show_date', $date)->orWhere('show_date', $dateSlash);
            })
            ->where('setlist', $title)
            ->exists();
    }

    /**
     * @param  mixed  $lineup
     */
    private function lineupIncludesIdol($lineup, string $idolShortname): bool
    {
        if (! is_array($lineup) || $idolShortname === '') {
            return false;
        }

        foreach ($lineup as $member) {
            if (! is_array($member) || ! isset($member['name'])) {
                continue;
            }

            if (strcasecmp(trim((string) $member['name']), $idolShortname) === 0) {
                return true;
            }
        }

        return false;
    }

    private function recordReference(string $referenceCode): void
    {
        TheaterReference::query()->firstOrCreate(
            ['reference_code' => $referenceCode],
            [
                'month' => now()->month,
                'year' => now()->year,
                'processed_at' => now(),
            ],
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchFromApi(string $url, string $apiKey, int $retry = 2): ?array
    {
        for ($attempt = 1; $attempt <= $retry; $attempt++) {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
                'X-API-KEY' => $apiKey,
            ])->get($url);

            if ($response->successful()) {
                $json = $response->json();

                if (is_array($json)) {
                    return $json;
                }
            }

            if ($attempt < $retry) {
                usleep(500000);
            }
        }

        return null;
    }
}
