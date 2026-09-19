<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

final class YoutubeRss
{
    private const FEED_URL = 'https://www.youtube.com/feeds/videos.xml';

    private const ATOM_NS = 'http://www.w3.org/2005/Atom';

    private const MEDIA_NS = 'http://search.yahoo.com/mrss/';

    private const YOUTUBE_NS = 'http://www.youtube.com/xml/schemas/2015';

    /**
     * Extract the playlist id from a YouTube URL.
     */
    public static function playlistId(string $url): ?string
    {
        $query = parse_url(trim($url), PHP_URL_QUERY);

        if (! is_string($query)) {
            return null;
        }

        parse_str($query, $params);

        $id = $params['list'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    /**
     * Latest videos of a playlist from its RSS feed, newest first.
     *
     * @return array<int, array{id: string, title: string, description: string, thumbnail: string|null, url: string, published: string|null}>
     */
    public static function videos(string $playlistUrl, int $limit = 7): array
    {
        $playlistId = self::playlistId($playlistUrl);

        if ($playlistId === null) {
            return [];
        }

        return array_slice(self::fetch($playlistId), 0, max(0, $limit));
    }

    /**
     * @return array<int, array{id: string, title: string, description: string, thumbnail: string|null, url: string, published: string|null}>
     */
    private static function fetch(string $playlistId): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; FansiteBot/1.0)',
                'Accept' => 'application/atom+xml, application/xml, text/xml',
            ])->timeout(10)->get(self::FEED_URL, ['playlist_id' => $playlistId]);
        } catch (Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $feed = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);

        if ($feed === false) {
            return [];
        }

        $videos = [];

        foreach ($feed->children(self::ATOM_NS)->entry as $entry) {
            $videoId = (string) $entry->children(self::YOUTUBE_NS)->videoId;

            if ($videoId === '') {
                continue;
            }

            $group = $entry->children(self::MEDIA_NS)->group;
            $description = isset($group->description) ? (string) $group->description : '';
            $thumbnail = '';

            if (isset($group->thumbnail)) {
                $attributes = $group->thumbnail->attributes();
                $thumbnail = isset($attributes['url']) ? (string) $attributes['url'] : '';
            }

            $published = (string) $entry->children(self::ATOM_NS)->published;

            $videos[] = [
                'id' => $videoId,
                'title' => (string) $entry->children(self::ATOM_NS)->title,
                'description' => self::cleanText($description),
                'thumbnail' => $thumbnail !== '' ? $thumbnail : null,
                'url' => 'https://www.youtube.com/watch?v='.$videoId,
                'published' => $published !== '' ? $published : null,
            ];
        }

        usort($videos, fn (array $a, array $b): int => strcmp((string) $b['published'], (string) $a['published']));

        return $videos;
    }

    private static function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }
}
