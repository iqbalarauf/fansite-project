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
     * @return array{showroom: bool, idn: bool}
     */
    public function status(string $memberName): array
    {
        $details = $this->details($memberName);

        return [
            'showroom' => $details['showroom']['live'],
            'idn' => $details['idn']['live'],
        ];
    }

    /**
     * Per-platform live state, stream URL, and member metadata for the given member.
     *
     * @return array{showroom: array{live: bool, url: string|null, name: string|null, image: string|null}, idn: array{live: bool, url: string|null, name: string|null, image: string|null}}
     */
    public function details(string $memberName): array
    {
        $memberName = trim($memberName);
        $baseUrl = rtrim((string) config('services.jkt48connect.url'), '/');
        $apiKey = (string) config('services.jkt48connect.key');

        if ($memberName === '' || $baseUrl === '' || $apiKey === '') {
            return $this->offlineDetails();
        }

        return Cache::remember(
            'member_live_'.md5(strtolower($memberName)),
            self::CACHE_SECONDS,
            fn (): array => $this->detailsFromItems($this->items($baseUrl, $apiKey), $memberName),
        );
    }

    /**
     * Raw live entries from the API that belong to the given member.
     *
     * @return array<int, array<string, mixed>>
     */
    public function matchingItems(string $memberName): array
    {
        $memberName = trim($memberName);
        $baseUrl = rtrim((string) config('services.jkt48connect.url'), '/');
        $apiKey = (string) config('services.jkt48connect.key');

        if ($memberName === '' || $baseUrl === '' || $apiKey === '') {
            return [];
        }

        return array_values(array_filter(
            $this->items($baseUrl, $apiKey),
            fn (mixed $item): bool => is_array($item) && $this->matchesMember($item, $memberName),
        ));
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
                ])->timeout(self::TIMEOUT_SECONDS)->get("{$baseUrl}/api/now-live");
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
     * @return array{showroom: array{live: bool, url: string|null, name: string|null, image: string|null}, idn: array{live: bool, url: string|null, name: string|null, image: string|null}}
     */
    private function detailsFromItems(array $items, string $memberName): array
    {
        $details = $this->offlineDetails();

        foreach ($items as $item) {
            if (! $this->matchesMember($item, $memberName)) {
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
     * so the idol shortname is matched against that normalized key first.
     *
     * @param  array<string, mixed>  $item
     */
    private function matchesMember(array $item, string $memberName): bool
    {
        $target = $this->normalize('jkt48_'.$memberName);

        if ($target === '') {
            return false;
        }

        $urlKey = $this->normalize((string) ($item['url_key'] ?? ''));

        if ($urlKey !== '') {
            return $urlKey === $target;
        }

        $candidates = array_unique([$this->normalize($memberName), $this->normalize($memberName.' JKT48')]);
        $name = $this->normalize((string) ($item['name'] ?? ''));

        return $name !== '' && in_array($name, $candidates, true);
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
