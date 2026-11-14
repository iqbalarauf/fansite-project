<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

final class CheckMemberLive
{
    private const CACHE_SECONDS = 20;

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
            self::CACHE_SECONDS,
            fn (): array => $this->detailsFromItems($this->items($baseUrl, $apiKey), $names),
        );
    }

    /**
     * Raw live entries from the API that belong to the given member.
     *
     * @param  string|array<int, string>  $memberNames  Idol name and/or shortname.
     * @return array<int, array<string, mixed>>
     */
    public function matchingItems(string|array $memberNames): array
    {
        $names = $this->normalizeNames($memberNames);
        $baseUrl = rtrim((string) config('services.jkt48connect.url'), '/');
        $apiKey = (string) config('services.jkt48connect.key');

        if ($names === [] || $baseUrl === '' || $apiKey === '') {
            return [];
        }

        return array_values(array_filter(
            $this->items($baseUrl, $apiKey),
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
        return Cache::remember(self::ITEMS_CACHE_KEY, self::CACHE_SECONDS, function () use ($baseUrl, $apiKey): array {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$apiKey,
                    'X-API-KEY' => $apiKey,
                ])->timeout(self::TIMEOUT_SECONDS)->get("{$baseUrl}/api/now_live", ['group' => 'jkt48']);
            } catch (Throwable) {
                return [];
            }

            if (! $response->successful()) {
                return [];
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                return [];
            }

            $items = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;

            return array_values(array_filter($items, 'is_array'));
        });
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
