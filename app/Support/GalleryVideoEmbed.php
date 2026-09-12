<?php

namespace App\Support;

final class GalleryVideoEmbed
{
    public const YOUTUBE = 'youtube';

    public const TWITTER = 'twitter';

    public const TIKTOK = 'tiktok';

    /**
     * @return array<int, string>
     */
    public static function platforms(): array
    {
        return [self::YOUTUBE, self::TWITTER, self::TIKTOK];
    }

    public static function label(?string $platform): string
    {
        return match ($platform) {
            self::YOUTUBE => 'YouTube',
            self::TWITTER => 'Twitter/X',
            self::TIKTOK => 'TikTok',
            default => '–',
        };
    }

    public static function detect(?string $url): ?string
    {
        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));

        if ($host === '') {
            return null;
        }

        return match (true) {
            str_contains($host, 'youtube.com'), str_contains($host, 'youtu.be') => self::YOUTUBE,
            str_contains($host, 'twitter.com'), str_contains($host, 'x.com') => self::TWITTER,
            str_contains($host, 'tiktok.com') => self::TIKTOK,
            default => null,
        };
    }

    public static function youtubeId(?string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', (string) $url, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public static function tiktokId(?string $url): ?string
    {
        if (preg_match('~tiktok\.com/(?:.*?/)?video/(\d+)~', (string) $url, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('~tiktok\.com/embed/(\d+)~', (string) $url, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
