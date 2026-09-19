<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Fallback source when the YouTube playlist RSS feed is unavailable.
 * Scrapes the playlist page's embedded ytInitialData (no API key required).
 */
final class YoutubePlaylistPage
{
    private const TIMEOUT = 15;

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';

    /**
     * @return array<int, array{id: string, title: string, description: string, thumbnail: string|null, url: string, published: string|null}>
     */
    public static function videos(string $playlistUrl, int $limit = 7): array
    {
        $playlistId = YoutubeRss::playlistId($playlistUrl);

        if ($playlistId === null) {
            return [];
        }

        $html = self::fetch($playlistId);
        $data = self::extractInitialData($html);

        if ($data === null) {
            return [];
        }

        $renderers = [];
        self::collectVideoRenderers($data, $renderers);

        $videos = [];

        foreach ($renderers as $renderer) {
            $videoId = self::videoId($renderer);

            if ($videoId === null) {
                continue;
            }

            $videos[] = [
                'id' => $videoId,
                'title' => self::title($renderer),
                'description' => self::description($renderer),
                'thumbnail' => 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg',
                'url' => 'https://www.youtube.com/watch?v='.$videoId,
                'published' => null,
            ];

            if (count($videos) >= max(0, $limit)) {
                break;
            }
        }

        return $videos;
    }

    private static function fetch(string $playlistId): string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(self::TIMEOUT)->get('https://www.youtube.com/playlist', ['list' => $playlistId]);
        } catch (Throwable) {
            return '';
        }

        return $response->successful() ? $response->body() : '';
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function extractInitialData(string $html): ?array
    {
        $position = strpos($html, 'ytInitialData');

        if ($position === false) {
            return null;
        }

        $start = strpos($html, '{', $position);

        if ($start === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escape = false;
        $length = strlen($html);
        $end = null;

        for ($index = $start; $index < $length; $index++) {
            $char = $html[$index];

            if ($inString) {
                if ($escape) {
                    $escape = false;
                } elseif ($char === '\\') {
                    $escape = true;
                } elseif ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
            } elseif ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;

                if ($depth === 0) {
                    $end = $index;

                    break;
                }
            }
        }

        if ($end === null) {
            return null;
        }

        $data = json_decode(substr($html, $start, $end - $start + 1), true);

        return is_array($data) ? $data : null;
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  array<int, array<string, mixed>>  $renderers
     */
    private static function collectVideoRenderers(array $node, array &$renderers): void
    {
        foreach ($node as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            if ($key === 'lockupViewModel' || $key === 'playlistVideoRenderer') {
                $renderers[] = $value;
            }

            self::collectVideoRenderers($value, $renderers);
        }
    }

    /**
     * @param  array<string, mixed>  $renderer
     */
    private static function videoId(array $renderer): ?string
    {
        $candidates = [
            $renderer['contentId'] ?? null,
            $renderer['videoId'] ?? null,
            $renderer['rendererContext']['commandContext']['onTap']['innertubeCommand']['watchEndpoint']['videoId'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) === 1) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $renderer
     */
    private static function title(array $renderer): string
    {
        $title = $renderer['metadata']['lockupMetadataViewModel']['title']['content']
            ?? $renderer['title']['runs'][0]['text']
            ?? $renderer['title']['simpleText']
            ?? '';

        return trim((string) $title);
    }

    /**
     * @param  array<string, mixed>  $renderer
     */
    private static function description(array $renderer): string
    {
        $runs = $renderer['descriptionSnippet']['runs'] ?? null;

        if (! is_array($runs)) {
            return '';
        }

        $text = collect($runs)->pluck('text')->implode('');

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }
}
