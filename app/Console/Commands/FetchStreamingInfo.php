<?php

namespace App\Console\Commands;

use App\Models\AboutSettings;
use App\Models\LiveStreaming;
use App\Support\Timezone;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

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
        $username = 'jkt48_'.$this->normalizeUsername($shortname);

        $payload = $this->fetchFromApi("{$baseUrl}/api/recent?page=1&perpage=25", $apiKey);
        if ($payload === null) {
            $this->error('Failed to fetch streaming data from JKT48Connect API.');

            return self::FAILURE;
        }

        $items = $this->recentItems($payload);
        if ($items === []) {
            $this->info("Tidak ada live streaming dari {$shortname} JKT48 hari ini");
            $this->info('Fetch completed.');

            return self::SUCCESS;
        }

        $matches = [];
        foreach ($items as $item) {
            $dataId = $item['data_id'] ?? $item['id'] ?? $item['_id'] ?? null;

            if (! $dataId || ! $this->matchesUsername($item, $username, $shortname)) {
                continue;
            }

            $matches[(string) $dataId] = $item;
        }

        if ($matches === []) {
            $this->info("Tidak ada live streaming dari {$shortname} JKT48 hari ini");
            $this->info('Fetch completed.');

            return self::SUCCESS;
        }

        $saved = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($matches as $dataId => $item) {
            if (LiveStreaming::query()->where('live_id', $dataId)->exists()) {
                $skipped++;
                $this->line("Live streaming {$dataId} sudah ada, dilewati.");

                continue;
            }

            $gifts = $this->fetchFromApi("{$baseUrl}/api/recent/".rawurlencode($dataId).'/gifts', $apiKey);

            if ($gifts === null) {
                $failed++;
                $this->warn("Gagal mengambil data gifts untuk live streaming {$dataId}.");

                continue;
            }

            if ($this->saveItem($item, $gifts)) {
                $saved++;
            } else {
                $skipped++;
            }
        }

        if ($saved === 0) {
            $this->info("Tidak ada data baru dari {$shortname} JKT48");
        } else {
            $this->info("{$saved} data live streaming ditambahkan, {$skipped} dilewati.");
        }

        if ($failed > 0) {
            $this->warn("{$failed} data live streaming gagal diambil.");
        }

        $this->info('Fetch completed.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $gifts
     */
    private function saveItem(array $item, array $gifts): bool
    {
        $liveId = $item['data_id'] ?? $item['id'] ?? $item['_id'] ?? null;

        if (! $liveId) {
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

        $startTime = $this->timestampToLocal($item['live_info']['date']['start'] ?? null);
        $endTime = $this->timestampToLocal($item['live_info']['date']['end'] ?? null);
        $liveDate = $startTime?->toDateString() ?? Timezone::nowLocal()->toDateString();

        LiveStreaming::query()->create([
            'live_id' => (string) $liveId,
            'platform' => $platform,
            'live_date' => $liveDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $this->durationMinutes($item, $startTime, $endTime),
            'max_viewers' => $this->integerValue($item['live_info']['viewers']['num'] ?? null),
            'comment_count' => $this->commentCount($gifts),
            'gift_count' => $this->giftCount($gifts),
            'total_gold' => $this->totalGold($item, $gifts),
            'youtube_url' => $this->youtubeUrl($item),
            'gifts' => $this->mapGifts($gifts['list'] ?? []),
            'top_senders' => $this->mapTopSenders($gifts),
            'additional_info' => $this->title($item),
        ]);

        $this->info("Saved live streaming: {$platform} - {$liveDate}");

        return true;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    private function recentItems(array $payload): array
    {
        $items = $payload['recents'] ?? $payload['data']['recents'] ?? $payload['items'] ?? $payload['data'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, 'is_array'));
    }

    /**
     * The recent endpoint exposes the IDN username as "jkt48_{shortname}".
     * Showroom entries have no username, so their member nickname is used.
     *
     * @param  array<string, mixed>  $item
     */
    private function matchesUsername(array $item, string $username, string $shortname): bool
    {
        $candidates = [
            $item['idn']['username'] ?? null,
            $item['showroom']['username'] ?? null,
            $item['username'] ?? null,
            $item['user']['username'] ?? null,
        ];

        $hasUsername = false;

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $hasUsername = true;

            if ($this->normalizeUsername($candidate) === $this->normalizeUsername($username)) {
                return true;
            }
        }

        if ($hasUsername) {
            return false;
        }

        $target = $this->normalizeUsername($shortname);
        $nickname = $this->normalizeUsername((string) ($item['member']['nickname'] ?? ''));
        $name = $this->normalizeUsername((string) ($item['member']['name'] ?? ''));

        return $target !== '' && ($nickname === $target || $name === $target);
    }

    private function normalizeUsername(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($value))) ?? '';
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function durationMinutes(array $item, ?Carbon $startTime, ?Carbon $endTime): ?int
    {
        $duration = $item['live_info']['duration'] ?? null;

        if (is_numeric($duration)) {
            return (int) round(((int) $duration) / 60000);
        }

        if ($startTime !== null && $endTime !== null) {
            return (int) round($startTime->diffInMinutes($endTime));
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $gifts
     */
    private function commentCount(array $gifts): ?int
    {
        $users = $gifts['users'] ?? null;

        if (! is_array($users)) {
            return null;
        }

        $count = 0;
        foreach ($users as $user) {
            if (is_array($user)) {
                $count += (int) ($user['comments'] ?? 0);
            }
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $gifts
     */
    private function giftCount(array $gifts): ?int
    {
        $entries = $gifts['gifts'] ?? null;

        if (! is_array($entries)) {
            return $this->integerValue($gifts['total_count'] ?? null);
        }

        $count = 0;
        foreach ($entries as $entry) {
            if (! is_array($entry) || ! is_array($entry['gifts'] ?? null)) {
                continue;
            }

            foreach ($entry['gifts'] as $gift) {
                if (is_array($gift)) {
                    $count += (int) ($gift['num'] ?? 0);
                }
            }
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $gifts
     */
    private function totalGold(array $item, array $gifts): ?int
    {
        $points = $this->integerValue($item['points'] ?? null);

        if ($points !== null && $points > 0) {
            return $points;
        }

        $reported = $this->integerValue($gifts['gold_summary']['total_gold'] ?? null);

        if ($reported !== null) {
            return $reported;
        }

        $total = 0;
        foreach (($gifts['gifts'] ?? []) as $entry) {
            if (! is_array($entry) || ! is_array($entry['gifts'] ?? null)) {
                continue;
            }

            foreach ($entry['gifts'] as $gift) {
                if (is_array($gift)) {
                    $total += (int) ($gift['point'] ?? 0) * (int) ($gift['num'] ?? 0);
                }
            }
        }

        return $total;
    }

    private function timestampToLocal(mixed $value): ?Carbon
    {
        if (is_numeric($value)) {
            return Carbon::createFromTimestampMs((int) $value, 'UTC')->timezone('Asia/Jakarta');
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->timezone('Asia/Jakarta');
        } catch (Throwable) {
            return null;
        }
    }

    private function integerValue(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function title(array $item): ?string
    {
        $title = $item['idn']['title'] ?? null;

        if (! filled($title)) {
            $title = $item['youtube_replay']['videos'][0]['title'] ?? null;
        }

        return filled($title) ? trim((string) $title) : null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function youtubeUrl(array $item): ?string
    {
        $url = $item['playback']['youtube_url'] ?? null;

        if (! filled($url)) {
            $url = $item['youtube_replay']['videos'][0]['url'] ?? null;
        }

        if (! filled($url)) {
            $url = $item['youtube_replay']['videos'][0]['youtube_url'] ?? null;
        }

        return filled($url) ? trim((string) $url) : null;
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

            $count = (int) ($gift['num'] ?? 0);

            if ($count === 0 && (int) ($gift['send_count'] ?? 0) === 0) {
                continue;
            }

            $goldPerUnit = (int) ($gift['point'] ?? 0);

            $mapped[] = [
                'name' => (string) ($gift['name'] ?? ''),
                'image_url' => $gift['img'] ?? ($gift['image_url'] ?? null),
                'gold_per_unit' => $goldPerUnit,
                'count' => $count,
                'total_gold' => $goldPerUnit * $count,
            ];
        }

        usort($mapped, fn (array $left, array $right): int => $right['total_gold'] <=> $left['total_gold']);

        return $mapped;
    }

    /**
     * @param  array<string, mixed>  $gifts
     * @return array<int, array{name: string, avatar: string|null, total_gold: int}>
     */
    private function mapTopSenders(array $gifts): array
    {
        $fans = $gifts['fans'] ?? null;

        if (is_array($fans) && $fans !== []) {
            $mapped = [];

            foreach (array_slice($fans, 0, 10) as $fan) {
                if (! is_array($fan)) {
                    continue;
                }

                $mapped[] = [
                    'name' => (string) ($fan['name'] ?? ''),
                    'avatar' => $fan['avatar_url'] ?? ($fan['avatar'] ?? null),
                    'total_gold' => (int) ($fan['gold'] ?? ($fan['total_gold'] ?? ($fan['observed_gold'] ?? 0))),
                ];
            }

            return $mapped;
        }

        $users = [];
        foreach (($gifts['users'] ?? []) as $user) {
            if (is_array($user) && isset($user['id'])) {
                $users[(string) $user['id']] = $user;
            }
        }

        $mapped = [];
        foreach (($gifts['gifts'] ?? []) as $entry) {
            if (! is_array($entry) || ! is_array($entry['gifts'] ?? null)) {
                continue;
            }

            $totalGold = 0;
            foreach ($entry['gifts'] as $gift) {
                if (is_array($gift)) {
                    $totalGold += (int) ($gift['point'] ?? 0) * (int) ($gift['num'] ?? 0);
                }
            }

            $userId = (string) ($entry['user_id'] ?? '');
            $user = $users[$userId] ?? [];

            $mapped[] = [
                'name' => (string) ($user['name'] ?? ''),
                'avatar' => $user['avatar_url'] ?? null,
                'total_gold' => $totalGold,
            ];
        }

        usort($mapped, fn (array $left, array $right): int => $right['total_gold'] <=> $left['total_gold']);

        return array_slice($mapped, 0, 10);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchFromApi(string $url, string $apiKey, int $retry = 2): ?array
    {
        for ($attempt = 1; $attempt <= $retry; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$apiKey,
                    'X-API-KEY' => $apiKey,
                ])->timeout(10)->get($url);

                if ($response->successful()) {
                    $json = $response->json();

                    if (is_array($json)) {
                        return $json;
                    }
                }
            } catch (Throwable) {
                // Retry below.
            }

            if ($attempt < $retry) {
                usleep(500000);
            }
        }

        return null;
    }
}
