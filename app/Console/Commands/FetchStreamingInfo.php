<?php

namespace App\Console\Commands;

use App\Models\AboutSettings;
use App\Models\LiveStreaming;
use App\Support\Timezone;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class FetchStreamingInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-streaming-info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch recent live streaming data from JKT48Connect API';

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

        $payload = $this->fetchFromApi("{$baseUrl}/api/v1/recent", $apiKey);
        if ($payload === null) {
            $this->error('Failed to fetch streaming data from JKT48Connect API.');

            return self::FAILURE;
        }

        $displayName = trim((string) $idolShortname).' JKT48';
        $targetName = $this->normalizeName($displayName);

        $items = $payload['data'] ?? [];
        if (! is_array($items) || $items === []) {
            $this->info("Tidak ada live streaming dari {$displayName} hari ini");
            $this->info('Fetch completed.');

            return self::SUCCESS;
        }

        $matched = 0;
        $saved = 0;

        foreach ($items as $item) {
            if (! is_array($item) || ! $this->memberMatches($item, $targetName)) {
                continue;
            }

            $matched++;

            if ($this->saveItem($item)) {
                $saved++;
            }
        }

        if ($matched === 0) {
            $this->info("Tidak ada live streaming dari {$displayName} hari ini");
        } else {
            $this->info("{$saved} data live streaming ditambahkan.");
        }

        $this->info('Fetch completed.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function memberMatches(array $item, string $targetName): bool
    {
        $memberName = $item['member']['name'] ?? null;

        return $memberName !== null && $this->normalizeName((string) $memberName) === $targetName;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function saveItem(array $item): bool
    {
        $liveId = $item['_id'] ?? $item['data_id'] ?? null;
        if (! $liveId) {
            return false;
        }

        $liveId = (string) $liveId;

        if (LiveStreaming::query()->where('live_id', $liveId)->exists()) {
            $this->error('Data Live sudah tersimpan sebelumnya');

            return false;
        }

        $platform = match (strtolower((string) ($item['type'] ?? ''))) {
            'idn' => 'IDN App',
            'showroom' => 'Showroom',
            default => null,
        };

        if ($platform === null) {
            return false;
        }

        $durationMs = $item['live_info']['duration'] ?? null;
        $duration = is_numeric($durationMs) ? (int) round(((float) $durationMs) / 60000) : null;

        $start = $item['live_info']['date']['start'] ?? null;
        $liveDate = $start
            ? Carbon::parse($start)->timezone('Asia/Jakarta')->toDateString()
            : Timezone::nowLocal()->toDateString();

        $title = $item['idn']['title'] ?? null;

        LiveStreaming::query()->create([
            'live_id' => $liveId,
            'platform' => $platform,
            'live_date' => $liveDate,
            'duration' => $duration,
            'additional_info' => $title !== null ? trim((string) $title) : null,
        ]);

        $this->info("Saved live streaming: {$platform} - {$liveDate}");

        return true;
    }

    private function normalizeName(string $name): string
    {
        return strtolower(preg_replace('/\s+/', '', $name) ?? '');
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
