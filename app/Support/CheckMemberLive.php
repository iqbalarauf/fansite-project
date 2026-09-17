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
        $memberName = trim($memberName);
        $baseUrl = rtrim((string) config('services.jkt48connect.url'), '/');
        $apiKey = (string) config('services.jkt48connect.key');

        if ($memberName === '' || $baseUrl === '' || $apiKey === '') {
            return $this->offline();
        }

        return Cache::remember(
            'member_live_'.md5(strtolower($memberName)),
            self::CACHE_SECONDS,
            fn (): array => $this->statusFromItems($this->items($baseUrl, $apiKey), $memberName),
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
                ])->timeout(self::TIMEOUT_SECONDS)->get("{$baseUrl}/api/v1/live");
            } catch (Throwable) {
                return [];
            }

            if (! $response->successful()) {
                return [];
            }

            $items = $response->json('data');

            if (! is_array($items)) {
                return [];
            }

            return array_values(array_filter($items, 'is_array'));
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{showroom: bool, idn: bool}
     */
    private function statusFromItems(array $items, string $memberName): array
    {
        $status = $this->offline();

        foreach ($items as $item) {
            if (! is_array($item) || ! $this->matchesMember($item, $memberName)) {
                continue;
            }

            $platform = strtolower(trim((string) ($item['platform'] ?? $item['type'] ?? '')));

            if ($platform === 'showroom') {
                $status['showroom'] = true;
            } elseif (in_array($platform, ['idn', 'idn app'], true)) {
                $status['idn'] = true;
            }
        }

        return $status;
    }

    /**
     * The live API returns member names with a "JKT48" suffix (e.g. "Oniel JKT48")
     * and a url_key like "jkt48_oniel", so matching must be normalized and suffix-aware.
     *
     * @param  array<string, mixed>  $item
     */
    private function matchesMember(array $item, string $memberName): bool
    {
        $target = $this->normalize($memberName);

        if ($target === '') {
            return false;
        }

        $candidates = array_unique([$target, $this->normalize($memberName.' JKT48')]);
        $keys = array_map(fn (string $candidate): string => 'jkt48'.$candidate, $candidates);

        $name = $this->normalize((string) ($item['name'] ?? ''));
        if ($name !== '' && in_array($name, $candidates, true)) {
            return true;
        }

        $urlKey = $this->normalize((string) ($item['url_key'] ?? ''));

        return $urlKey !== '' && in_array($urlKey, $keys, true);
    }

    private function normalize(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($value))) ?? '';
    }

    /**
     * @return array{showroom: bool, idn: bool}
     */
    private function offline(): array
    {
        return ['showroom' => false, 'idn' => false];
    }
}
