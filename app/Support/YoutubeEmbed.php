<?php

namespace App\Support;

final class YoutubeEmbed
{
    /**
     * Build an embeddable YouTube URL from a playlist (or video) link.
     */
    public static function embedUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $listId = self::queryParam($url, 'list');

        if ($listId !== null) {
            return 'https://www.youtube.com/embed/videoseries?list='.rawurlencode($listId);
        }

        $videoId = self::queryParam($url, 'v') ?? self::shortUrlId($url) ?? self::embedId($url);

        return $videoId !== null ? 'https://www.youtube.com/embed/'.rawurlencode($videoId) : null;
    }

    private static function queryParam(string $url, string $key): ?string
    {
        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($query)) {
            return null;
        }

        parse_str($query, $params);

        $value = $params[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private static function shortUrlId(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($host) || ! is_string($path)) {
            return null;
        }

        if (! in_array(strtolower($host), ['youtu.be', 'www.youtu.be'], true)) {
            return null;
        }

        $id = trim($path, '/');

        return $id !== '' ? $id : null;
    }

    private static function embedId(string $url): ?string
    {
        if (preg_match('#youtube\.com/embed/([A-Za-z0-9_-]+)#', $url, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
