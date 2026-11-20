<?php

namespace App\Console\Commands;

use App\Models\AboutSettings;
use App\Models\ShowTeater;
use App\Models\TheaterReference;
use App\Support\SettingBag;
use App\Support\ShowTeaterNormalizer;
use App\Support\ShowTeaterUnitSongPredictor;
use App\Support\Timezone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchTheaterShows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-theater-shows {--source=jkt48connect : Data source: jkt48connect or fansight}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch theater shows data from JKT48Connect API or FANSIGHT API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Reset bulanan: hapus reference lama, pertahankan yang dipakai show yang belum lewat.
        $now = Timezone::nowLocal();
        TheaterReference::deleteOldReferences($now->month, $now->year);

        $source = (string) $this->option('source');

        if (! in_array($source, ['jkt48connect', 'fansight'], true)) {
            $this->error('Invalid source. Use jkt48connect or fansight.');

            return self::FAILURE;
        }

        return $source === 'fansight'
            ? $this->fetchFromFansight()
            : $this->fetchFromConnect();
    }

    /**
     * Fetch shows from the JKT48Connect API.
     */
    private function fetchFromConnect(): int
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

        $payload = $this->fetchFromApi("{$baseUrl}/api/theater", $apiKey);
        if ($payload === null) {
            $this->error('Failed to fetch theater data from JKT48Connect API.');

            return self::FAILURE;
        }

        $shows = $payload['theater'] ?? $payload['data'] ?? [];
        if (! is_array($shows) || $shows === []) {
            $this->error('No theater data found.');

            return self::FAILURE;
        }

        $unitSongPredictions = SettingBag::showTeaterPredictorEnabled()
            ? app(ShowTeaterUnitSongPredictor::class)->mapBySetlist()
            : [];

        foreach ($this->sortShowsByDate($shows) as $show) {
            if (is_array($show)) {
                $this->processShow($show, trim((string) $idolShortname), $unitSongPredictions);
            }
        }

        $this->info('Fetch completed.');

        return self::SUCCESS;
    }

    /**
     * Fetch shows from the FANSIGHT API.
     *
     * The API is queried by idol name (spaces URL-encoded) and already returns
     * only the shows whose lineup matches the idol.
     */
    private function fetchFromFansight(): int
    {
        $baseUrl = rtrim((string) config('services.fansight.url'), '/');

        if ($baseUrl === '') {
            $this->error('FANSIGHT API is not configured (API_FANSIGHT_URL).');

            return self::FAILURE;
        }

        $idolName = AboutSettings::query()->where('key', 'idol_name')->value('value');
        if (! filled($idolName)) {
            $this->error('Idol name not found in about_settings.');

            return self::FAILURE;
        }

        $idolName = trim((string) $idolName);
        // Normalisasi whitespace (termasuk NBSP) agar query cocok dengan API.
        $idolName = (string) preg_replace('/\s+/u', ' ', $idolName);

        $endpoint = "{$baseUrl}/shows";

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->timeout(15)
                ->get($endpoint, ['idol_name' => $idolName]);
        } catch (\Throwable $exception) {
            $this->error("Failed to reach FANSIGHT API ({$endpoint}): ".$exception->getMessage());

            return self::FAILURE;
        }

        if (! $response->successful()) {
            $this->error("FANSIGHT API returned HTTP {$response->status()} for {$endpoint}.");

            return self::FAILURE;
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            $this->error('FANSIGHT API returned a non-JSON response.');

            return self::FAILURE;
        }

        $shows = $payload['data'] ?? [];
        if (! is_array($shows) || $shows === []) {
            $url = $response->effectiveUri();
            $this->warn("Tidak ada data show untuk {$idolName}.");
            $this->line("URL dipanggil: {$url}");
            $this->line('Isi respons: '.mb_substr((string) $response->body(), 0, 500));

            return self::SUCCESS;
        }

        $unitSongPredictions = SettingBag::showTeaterPredictorEnabled()
            ? app(ShowTeaterUnitSongPredictor::class)->mapBySetlist()
            : [];

        $saved = 0;

        foreach ($this->sortShowsByDate($shows) as $show) {
            if (is_array($show)) {
                $saved += $this->processFansightShow($show, $idolName, $unitSongPredictions);
            }
        }

        $this->info("FANSIGHT scan completed. {$saved} show(s) saved.");
        $this->info('Fetch completed.');

        return self::SUCCESS;
    }

    /**
     * Persist a show coming from the FANSIGHT API.
     *
     * @param  array<string, mixed>  $show
     * @param  array<string, array{unit_song: ?string, is_global_center: bool, is_us_center: bool}>  $unitSongPredictions
     * @return int Number of saved show rows (0 or 1).
     */
    private function processFansightShow(array $show, string $idolName, array $unitSongPredictions): int
    {
        if (($show['type'] ?? null) !== 'SHOW') {
            return 0;
        }

        $referenceCode = $show['reference_code'] ?? null;
        $date = $show['date'] ?? null;
        $title = isset($show['title']) ? trim((string) $show['title']) : '';

        if (! is_string($referenceCode) || $referenceCode === '' || ! is_string($date) || $date === '' || $title === '') {
            return 0;
        }

        // API sudah memfilter, tapi verifikasi lineup agar aman.
        if (! $this->fansightShowIncludesIdol($show, $idolName)) {
            return 0;
        }

        $alreadySynced = $this->showExists($date, $title);
        $prediction = $unitSongPredictions[$title] ?? null;

        if (TheaterReference::query()->where('reference_code', $referenceCode)->exists()) {
            if ($alreadySynced) {
                $this->attachReferenceCode($date, $title, $referenceCode, $prediction);
                $this->error('Show terbaru sudah tersinkronisasi');
            }

            return 0;
        }

        $this->recordReference($referenceCode);

        if ($alreadySynced) {
            $this->attachReferenceCode($date, $title, $referenceCode, $prediction);
            $this->error('Show terbaru sudah tersinkronisasi');

            return 0;
        }

        $newShowId = (int) (ShowTeater::withTrashed()->max('show_id') ?? 0) + 1;

        $attributes = [
            'show_id' => $newShowId,
            'show_date' => $date,
            'setlist' => $title,
            'unit_song' => $prediction['unit_song'] ?? null,
            'reference_code' => $referenceCode,
            'is_scraped_data' => 1,
        ];

        if ($prediction['is_global_center'] ?? false) {
            $attributes['is_global_center'] = 1;
        }

        if ($prediction['is_us_center'] ?? false) {
            $attributes['is_us_center'] = 1;
        }

        ShowTeater::query()->create($attributes);

        app(ShowTeaterNormalizer::class)->syncShow($newShowId);

        $this->info("Saved show: {$newShowId} - {$date} - {$title}");

        return 1;
    }

    /**
     * @param  array<string, mixed>  $show
     */
    private function fansightShowIncludesIdol(array $show, string $idolName): bool
    {
        $matched = $show['matched_members'] ?? null;

        if (is_array($matched) && $matched !== []) {
            return true;
        }

        return $this->lineupIncludesIdolName($show['members'] ?? $show['lineup'] ?? [], $idolName);
    }

    /**
     * @param  mixed  $lineup
     */
    private function lineupIncludesIdolName($lineup, string $idolName): bool
    {
        if (! is_array($lineup) || $idolName === '') {
            return false;
        }

        foreach ($lineup as $member) {
            if (! is_array($member) || ! isset($member['name'])) {
                continue;
            }

            if (strcasecmp(trim((string) $member['name']), $idolName) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $show
     * @param  array<string, string>  $unitSongPredictions
     */
    private function processShow(array $show, string $idolShortname, array $unitSongPredictions): void
    {
        // Reference code diambil dari id/url, fallback ke reference_code atau link ?code=.
        $referenceCode = $this->extractReferenceCode(
            $show['url'] ?? $show['link'] ?? null,
            $show['reference_code'] ?? $show['id'] ?? null,
        );

        if (! $referenceCode) {
            return;
        }

        $date = $this->normalizeShowDate($show['date'] ?? $show['start_date'] ?? null);
        $title = isset($show['title']) ? trim((string) $show['title']) : '';

        if ($date === null || $title === '') {
            return;
        }

        $alreadySynced = $this->showExists($date, $title);
        $prediction = $unitSongPredictions[$title] ?? null;
        $event = $this->seitansaiEvent($show['seitansai'] ?? null);

        // Reference sudah pernah diproses pada bulan berjalan.
        if (TheaterReference::query()->where('reference_code', $referenceCode)->exists()) {
            if ($alreadySynced) {
                $this->attachReferenceCode($date, $title, $referenceCode, $prediction, $event);
                $this->error('Show terbaru sudah tersinkronisasi');
            }

            return;
        }

        if (! $this->lineupIncludesIdol($show['members'] ?? $show['lineup'] ?? [], $idolShortname)) {
            return;
        }

        // Endpoint lama memakai field type (SHOW/EVENT); endpoint /api/theater tidak mengirimnya.
        if (array_key_exists('type', $show) && $show['type'] !== null && $show['type'] !== 'SHOW') {
            return;
        }

        $this->recordReference($referenceCode);

        if ($alreadySynced) {
            $this->attachReferenceCode($date, $title, $referenceCode, $prediction, $event);
            $this->error('Show terbaru sudah tersinkronisasi');

            return;
        }

        // Hitung dari seluruh baris (termasuk yang ter-soft delete) agar show_id tidak menimpa PK.
        $newShowId = (int) (ShowTeater::withTrashed()->max('show_id') ?? 0) + 1;

        // Unit song: pakai dari API bila ada, jika tidak prediksi dari show terakhir setlist sama.
        $apiUnitSong = $show['unit_song'] ?? null;
        $unitSong = is_string($apiUnitSong) && trim($apiUnitSong) !== ''
            ? trim($apiUnitSong)
            : ($prediction['unit_song'] ?? null);

        $attributes = [
            'show_id' => $newShowId,
            'show_date' => $date,
            'setlist' => $title,
            'unit_song' => $unitSong,
            'reference_code' => $referenceCode,
            'is_scraped_data' => 1,
        ];

        if ($event !== null) {
            $attributes['is_the_show_has_event'] = $event;
        }

        // Center (input manual, tidak ada di API): ikut prediksi bila show sebelumnya center.
        if ($prediction['is_global_center'] ?? false) {
            $attributes['is_global_center'] = 1;
        }

        if ($prediction['is_us_center'] ?? false) {
            $attributes['is_us_center'] = 1;
        }

        ShowTeater::query()->create($attributes);

        app(ShowTeaterNormalizer::class)->syncShow($newShowId);

        $this->info("Saved show: {$newShowId} - {$date} - {$title}");
    }

    /**
     * Isi reference_code, event & center pada show yang sudah ada namun belum terisi.
     *
     * @param  array{unit_song: ?string, is_global_center: bool, is_us_center: bool}|null  $prediction
     */
    private function attachReferenceCode(string $date, string $title, string $referenceCode, ?array $prediction = null, ?string $event = null): void
    {
        $dateSlash = str_replace('-', '/', $date);

        $matchShow = fn () => ShowTeater::withTrashed()
            ->where(function ($query) use ($date, $dateSlash): void {
                $query->where('show_date', $date)->orWhere('show_date', $dateSlash);
            })
            ->where('setlist', $title);

        $matchShow()
            ->whereNull('reference_code')
            ->update(['reference_code' => $referenceCode]);

        // Event diisi hanya bila belum ada nilainya, agar input manual tidak tertimpa.
        if ($event !== null) {
            $matchShow()->whereNull('is_the_show_has_event')->update(['is_the_show_has_event' => $event]);
        }

        // Center diisi hanya bila belum ada nilainya (NULL), agar input manual 0 tidak tertimpa.
        if ($prediction['is_global_center'] ?? false) {
            $matchShow()->whereNull('is_global_center')->update(['is_global_center' => 1]);
        }

        if ($prediction['is_us_center'] ?? false) {
            $matchShow()->whereNull('is_us_center')->update(['is_us_center' => 1]);
        }
    }

    /**
     * Format seitansai menjadi label event "STS {name}" (null bila tidak ada nama valid).
     */
    private function seitansaiEvent(mixed $seitansai): ?string
    {
        if (! is_array($seitansai)) {
            return null;
        }

        $names = [];

        foreach ($seitansai as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $name = isset($entry['name']) ? trim((string) $entry['name']) : '';

            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names === [] ? null : 'STS '.implode(', ', array_unique($names));
    }

    /**
     * Ubah tanggal API (ISO 8601 atau Y-m-d) menjadi tanggal lokal Y-m-d.
     */
    private function normalizeShowDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Timezone::toLocal(trim($value))?->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Urutkan show dari tanggal paling lama ke paling baru agar show_id mengikuti kronologi.
     *
     * @param  array<int, mixed>  $shows
     * @return array<int, mixed>
     */
    private function sortShowsByDate(array $shows): array
    {
        $keyed = [];

        foreach ($shows as $index => $show) {
            $date = is_array($show)
                ? $this->normalizeShowDate($show['date'] ?? $show['start_date'] ?? null)
                : null;

            $keyed[] = ['index' => $index, 'date' => $date, 'show' => $show];
        }

        usort($keyed, function (array $left, array $right): int {
            if ($left['date'] === null || $right['date'] === null) {
                return $left['date'] === $right['date'] ? $left['index'] <=> $right['index'] : ($left['date'] === null ? 1 : -1);
            }

            return $left['date'] <=> $right['date'] ?: $left['index'] <=> $right['index'];
        });

        return array_values(array_map(fn (array $entry): mixed => $entry['show'], $keyed));
    }

    /**
     * Ambil reference code dari link (mis. ...?code=SH79AC); fallback ke field reference_code.
     *
     * @param  mixed  $link
     * @param  mixed  $fallback
     */
    private function extractReferenceCode($link, $fallback): ?string
    {
        if (is_string($link) && $link !== '') {
            $query = parse_url($link, PHP_URL_QUERY);

            if (is_string($query)) {
                parse_str($query, $params);

                if (isset($params['code']) && is_string($params['code']) && $params['code'] !== '') {
                    return $params['code'];
                }
            }

            // Link berupa kode langsung (tanpa skema/separator).
            if (! str_contains($link, '/') && ! str_contains($link, '?')) {
                return $link;
            }
        }

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
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
                'month' => Timezone::nowLocal()->month,
                'year' => Timezone::nowLocal()->year,
                'processed_at' => now(),
            ],
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchFromApi(string $url, string $apiKey = '', int $retry = 2): ?array
    {
        $headers = ['Accept' => 'application/json'];

        if ($apiKey !== '') {
            $headers['Authorization'] = 'Bearer '.$apiKey;
            $headers['X-API-KEY'] = $apiKey;
        }

        for ($attempt = 1; $attempt <= $retry; $attempt++) {
            $response = Http::withHeaders($headers)->get($url);

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
