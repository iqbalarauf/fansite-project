<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

final class CheckMemberLive
{
    private const CACHE_SECONDS = 20;

    private const TIMEOUT_SECONDS = 3;

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
            fn (): array => $this->fetch($baseUrl, $apiKey, $memberName),
        );
    }

    /**
     * @return array{showroom: bool, idn: bool}
     */
    private function fetch(string $baseUrl, string $apiKey, string $memberName): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
                'X-API-KEY' => $apiKey,
            ])->timeout(self::TIMEOUT_SECONDS)->get("{$baseUrl}/api/v1/live");
        } catch (Throwable) {
            return $this->offline();
        }

        if (! $response->successful()) {
            return $this->offline();
        }

        $items = $response->json('data');
        if (! is_array($items)) {
            return $this->offline();
        }

        $status = $this->offline();

        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['name'])) {
                continue;
            }

            if (strcasecmp(trim((string) $item['name']), $memberName) !== 0) {
                continue;
            }

            $platform = strtolower(trim((string) ($item['platform'] ?? '')));

            if ($platform === 'showroom') {
                $status['showroom'] = true;
            } elseif ($platform === 'idn') {
                $status['idn'] = true;
            }
        }

        return $status;
    }

    /**
     * @return array{showroom: bool, idn: bool}
     */
    private function offline(): array
    {
        return ['showroom' => false, 'idn' => false];
    }
}
