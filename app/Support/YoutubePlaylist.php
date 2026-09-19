<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class YoutubePlaylist
{
    private const CACHE_MINUTES = 180;

    private const NEGATIVE_CACHE_MINUTES = 10;

    /**
     * Playlist videos, cached. RSS is tried first (includes descriptions and
     * ordering by newest); the playlist page is used as a no-API-key fallback.
     *
     * @return array<int, array{id: string, title: string, description: string, thumbnail: string|null, url: string, published: string|null}>
     */
    public static function videos(string $playlistUrl, int $limit = 7): array
    {
        $playlistId = YoutubeRss::playlistId($playlistUrl);

        if ($playlistId === null) {
            return [];
        }

        $cacheKey = "youtube_playlist_{$playlistId}";
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return array_slice($cached, 0, max(0, $limit));
        }

        $videos = YoutubeRss::videos($playlistUrl, $limit);

        if ($videos === []) {
            $videos = YoutubePlaylistPage::videos($playlistUrl, $limit);
        }

        Cache::put(
            $cacheKey,
            $videos,
            $videos === [] ? now()->addMinutes(self::NEGATIVE_CACHE_MINUTES) : now()->addMinutes(self::CACHE_MINUTES),
        );

        return array_slice($videos, 0, max(0, $limit));
    }
}
