<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

final class CheckMemberLive
{
    /**
     * Reuse the computed per-member state briefly so live URL/image changes are
     * picked up quickly without hitting the API again.
     */
    private const DETAILS_CACHE_SECONDS = 20;

    /**
     * The /api/now_live payload is refreshed by the scheduler every 10 minutes.
     * Keep page-side cache slightly longer so a page request between two
     * scheduler runs never triggers an extra API call.
     */
    private const ITEMS_CACHE_SECONDS = 660;

    private const TIMEOUT_SECONDS = 3;

    private const ITEMS_CACHE_KEY = 'member_live_items';

    /**
     * Determine which live platforms are currently streaming for the given member.
     *
     * @param  string|array<int, string>  $memberNames
     * @return array{showroom: bool, idn: bool}
     */
    public function status(string|array $memberNames): array
    {
        $details = $this->details($memberNames);

        return [
            'showroom' => $details['showroom']['live'],
            'idn' => $details['idn']['live'],
        ];
    }

    /**
     * Per-platform live state, stream URL, and member metadata for the given member.
     *
     * @param  string|array<int, string>  $memberNames  Idol name and/or shortname.
     * @return array{showroom: array{live: bool, url: string|null, name: string|null, image: string|null}, idn: array{live: bool, url: string|null, name: string|null, image: string|null}}
     */
    public function details(string|array $memberNames): array
    {
        $names = $this->normalizeNames($memberNames);
        $baseUrl = rtrim((string) config('services.jkt48connect.url'), '/');
        $apiKey = (string) config('services.jkt48connect.key');

        if ($names === [] || $baseUrl === '' || $apiKey === '') {
            return $this->offlineDetails();
        }

        return Cache::remember(
            'member_live_'.md5(implode('|', $names)),
            self::DETAILS_CACHE_SECONDS,
            fn (): array => $this->detailsFromItems($this->items($baseUrl, $apiKey), $names),
        );
    }

    /**
     * Raw live entries from the API that belong to the given member.
     *
     * @param  string|array<int, string>  $memberNames  Idol name and/or shortname.
     * @param  bool  $refresh  Bypass the shared cache and fetch a fresh payload.
     * @return array<int, array<string, mixed>>
     */
    public function matchingItems(string|array $memberNames, bool $refresh = false): array
    {
        $names = $this->normalizeNames($memberNames);
        $baseUrl = rtrim((string) config('services.jkt48connect.url'), '/');
        $apiKey = (string) config('services.jkt48connect.key');

        if ($names === [] || $baseUrl === '' || $apiKey === '') {
            return [];
        }

        $items = $refresh
            ? $this->refreshItems($baseUrl, $apiKey)
            : $this->items($baseUrl, $apiKey);

        return array_values(array_filter(
            $items,
            fn (mixed $item): bool => is_array($item) && $this->matchesMember($item, $names),
        ));
    }

    /**
     * @param  string|array<int, string>  $memberNames
     * @return array<int, string>
     */
    private function normalizeNames(string|array $memberNames): array
    {
        $names = is_array($memberNames) ? $memberNames : [$memberNames];
        $normalized = [];

        foreach ($names as $name) {
            if (! is_string($name)) {
                continue;
            }

            $name = trim($name);
            $key = $this->normalize($name);

            if ($key !== '') {
                $normalized[$key] = $name;
            }
        }

        ksort($normalized);

        return array_values($normalized);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function items(string $baseUrl, string $apiKey): array
    {
        return Cache::remember(
            self::ITEMS_CACHE_KEY,
            self::ITEMS_CACHE_SECONDS,
            fn (): array => $this->fetchItems($baseUrl, $apiKey) ?? [],
        );
    }

    /**
     * Fetch a fresh payload and replace the shared cache entry.
     *
     * If the API call fails, keep the last successful payload instead of
     * caching an empty response for the whole scheduler window.
     *
     * @return array<int, array<string, mixed>>
     */
    private function refreshItems(string $baseUrl, string $apiKey): array
    {
        $items = $this->fetchItems($baseUrl, $apiKey);

        if ($items === null) {
            return $this->items($baseUrl, $apiKey);
        }

        Cache::put(self::ITEMS_CACHE_KEY, $items, self::ITEMS_CACHE_SECONDS);

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function fetchItems(string $baseUrl, string $apiKey): ?array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
                'X-API-KEY' => $apiKey,
            ])->timeout(self::TIMEOUT_SECONDS)->get("{$baseUrl}/api/now_live", ['group' => 'jkt48']);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            return null;
        }

        $items = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;

        return array_values(array_filter($items, 'is_array'));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, string>  $memberNames
     * @return array{showroom: array{live: bool, url: string|null, name: string|null, image: string|null}, idn: array{live: bool, url: string|null, name: string|null, image: string|null}}
     */
    private function detailsFromItems(array $items, array $memberNames): array
    {
        $details = $this->offlineDetails();

        foreach ($items as $item) {
            if (! $this->matchesMember($item, $memberNames)) {
                continue;
            }

            $platform = strtolower(trim((string) ($item['type'] ?? $item['platform'] ?? '')));

            $key = match (true) {
                $platform === 'showroom' => 'showroom',
                in_array($platform, ['idn', 'idn app'], true) => 'idn',
                default => null,
            };

            if ($key === null) {
                continue;
            }

            $details[$key]['live'] = true;

            if ($details[$key]['name'] === null) {
                $name = trim((string) ($item['name'] ?? ''));

                if ($name !== '') {
                    $details[$key]['name'] = $name;
                }
            }

            if ($details[$key]['image'] === null) {
                $image = trim((string) ($item['img'] ?? ($item['image'] ?? '')));

                if ($image !== '') {
                    $details[$key]['image'] = $image;
                }
            }

            if ($details[$key]['url'] === null) {
                $url = $this->streamUrl($item);

                if ($url !== null) {
                    $details[$key]['url'] = $url;
                }
            }
        }

        return $details;
    }

    /**
     * The live API may list the same IDN session twice: one entry holds the
     * streaming URLs and the other has an empty list, so only accept real URLs.
     *
     * @param  array<string, mixed>  $item
     */
    private function streamUrl(array $item): ?string
    {
        $direct = $item['stream_url'] ?? null;

        if (is_string($direct) && $direct !== '') {
            return $direct;
        }

        foreach (['streaming_url_list', 'streaming'] as $key) {
            $list = $item[$key] ?? null;

            if (! is_array($list)) {
                continue;
            }

            foreach ($list as $entry) {
                $url = is_array($entry) ? ($entry['url'] ?? null) : null;

                if (is_string($url) && $url !== '') {
                    return $url;
                }
            }
        }

        return null;
    }

    /**
     * Live entries expose a url_key such as "jkt48_oniel" (or "JKT48_Nayla"),
     * while the display name can be the idol full name or shortname. Match
     * either the normalized url_key or the name so both settings work.
     *
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $memberNames
     */
    private function matchesMember(array $item, array $memberNames): bool
    {
        $urlKey = $this->normalize((string) ($item['url_key'] ?? ''));
        $name = $this->normalize((string) ($item['name'] ?? ''));

        foreach ($memberNames as $memberName) {
            $target = $this->normalize($memberName);

            if ($target === '') {
                continue;
            }

            if ($urlKey !== '' && $urlKey === $this->normalize('jkt48_'.$memberName)) {
                return true;
            }

            if ($name !== '' && ($name === $target || $name === $this->normalize($memberName.' JKT48'))) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($value))) ?? '';
    }

    /**
     * @return array{showroom: array{live: bool, url: string|null, name: string|null, image: string|null}, idn: array{live: bool, url: string|null, name: string|null, image: string|null}}
     */
    private function offlineDetails(): array
    {
        return [
            'showroom' => ['live' => false, 'url' => null, 'name' => null, 'image' => null],
            'idn' => ['live' => false, 'url' => null, 'name' => null, 'image' => null],
        ];
    }
}
