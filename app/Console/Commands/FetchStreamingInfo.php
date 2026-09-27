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
    protected $description = 'Fetch recent live streaming detail from JKT48Connect API';

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

        $shortname = trim((string) $idolShortname);
        $url = "{$baseUrl}/api/v1/recent/detail?name=".rawurlencode($shortname);

        $payload = $this->fetchFromApi($url, $apiKey);
        if ($payload === null) {
            $this->error('Failed to fetch streaming data from JKT48Connect API.');

            return self::FAILURE;
        }

        $items = $payload['data']['items'] ?? $payload['items'] ?? $payload['data'] ?? [];
        if (! is_array($items) || $items === []) {
            $this->info("Tidak ada live streaming dari {$shortname} JKT48 hari ini");
            $this->info('Fetch completed.');

            return self::SUCCESS;
        }

        $saved = 0;
        $updated = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $result = $this->saveItem($item);

            if ($result === 'created') {
                $saved++;
            } elseif ($result === 'updated') {
                $updated++;
            }
        }

        if ($saved === 0 && $updated === 0) {
            $this->info("Tidak ada data baru dari {$shortname} JKT48");
        } else {
            $this->info("{$saved} data live streaming ditambahkan, {$updated} diperbarui.");
        }

        $this->info('Fetch completed.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return 'created'|'updated'|'skipped'
     */
    private function saveItem(array $item): string
    {
        $liveId = $item['id'] ?? $item['_id'] ?? $item['data_id'] ?? null;
        if (! $liveId) {
            return 'skipped';
        }

        $liveId = (string) $liveId;

        $platform = match (strtolower((string) ($item['type'] ?? ''))) {
            'idn' => 'IDN App',
            'showroom' => 'Showroom',
            default => null,
        };

        if ($platform === null) {
            return 'skipped';
        }

        $startTime = $this->timestampToLocal($item['start_time'] ?? null);
        $endTime = $this->timestampToLocal($item['end_time'] ?? null);

        $liveDate = $startTime?->toDateString() ?? Timezone::nowLocal()->toDateString();

        $duration = null;
        if ($startTime !== null && $endTime !== null) {
            $duration = (int) round($startTime->diffInMinutes($endTime));
        }

        $existing = LiveStreaming::query()->where('live_id', $liveId)->first();

        $attributes = [
            'platform' => $platform,
            'live_date' => $liveDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $duration,
            'max_viewers' => isset($item['max_viewers']) ? (int) $item['max_viewers'] : null,
            'comment_count' => isset($item['comment_count']) ? (int) $item['comment_count'] : null,
            'gift_count' => isset($item['gift_count']) ? (int) $item['gift_count'] : null,
            'total_gold' => isset($item['total_gold']) ? (int) $item['total_gold'] : null,
            'youtube_url' => $item['youtube_url'] ?? null,
            'gifts' => $this->mapGifts($item['gifts_summary'] ?? []),
            'top_senders' => $this->mapTopSenders($item['top_senders'] ?? []),
            'additional_info' => $this->title($item),
        ];

        if ($existing !== null) {
            $existing->update($attributes);

            return 'updated';
        }

        LiveStreaming::query()->create(['live_id' => $liveId] + $attributes);

        $this->info("Saved live streaming: {$platform} - {$liveDate}");

        return 'created';
    }

    /**
     * @param  mixed  $value  epoch milliseconds
     */
    private function timestampToLocal(mixed $value): ?Carbon
    {
        if (! is_numeric($value)) {
            return null;
        }

        return Carbon::createFromTimestampMs((int) $value, 'UTC')->timezone('Asia/Jakarta');
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function title(array $item): ?string
    {
        $title = $item['title'] ?? $item['idn']['title'] ?? null;

        return filled($title) ? trim((string) $title) : null;
    }

    /**
     * @return array<int, array{name: string, image_url: string|null, gold_per_unit: int, count: int, total_gold: int}>
     */
    private function mapGifts(mixed $gifts): array
    {
        if (! is_array($gifts)) {
            return [];
        }

        $mapped = [];

        foreach ($gifts as $gift) {
            if (! is_array($gift)) {
                continue;
            }

            $mapped[] = [
                'name' => (string) ($gift['name'] ?? ''),
                'image_url' => $gift['image_url'] ?? null,
                'gold_per_unit' => (int) ($gift['gold_per_unit'] ?? 0),
                'count' => (int) ($gift['count'] ?? 0),
                'total_gold' => (int) ($gift['total_gold'] ?? 0),
            ];
        }

        return $mapped;
    }

    /**
     * @return array<int, array{name: string, avatar: string|null, total_gold: int}>
     */
    private function mapTopSenders(mixed $senders): array
    {
        if (! is_array($senders)) {
            return [];
        }

        $mapped = [];

        foreach (array_slice($senders, 0, 10) as $sender) {
            if (! is_array($sender)) {
                continue;
            }

            $mapped[] = [
                'name' => (string) ($sender['name'] ?? ''),
                'avatar' => $sender['avatar'] ?? null,
                'total_gold' => (int) ($sender['total_gold'] ?? 0),
            ];
        }

        return $mapped;
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
