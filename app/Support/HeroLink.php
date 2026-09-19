<?php

namespace App\Support;

use App\Models\CustomPage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

final class HeroLink
{
    public const TYPES = ['url', 'page', 'list'];

    /**
     * Built-in list pages available as a hero button target.
     *
     * @return array<string, string>
     */
    public static function listPages(): array
    {
        return [
            'home' => 'Beranda',
            'about.idol' => 'About Idol ('.self::aboutPath(self::idolSlug()).')',
            'about.fansite' => 'About Fansite ('.self::aboutPath(self::fanbaseSlug()).')',
            'schedule.index' => 'Jadwal',
            'gallery.index' => 'Galeri',
            'timeline.index' => 'Timeline',
            'news.index' => 'News',
            'blog.index' => 'Blog',
            'magazine.index' => 'Majalah',
            'trivia.index' => 'Trivia',
            'photobooth.show' => 'Photobooth',
        ];
    }

    /**
     * Resolve a stored hero button link into a usable URL.
     *
     * @param  string  $type  One of "url", "page", or "list".
     */
    public static function resolve(string $type, string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return match ($type) {
            'page' => self::pageUrl($value),
            'list' => self::listUrl($value),
            default => $value,
        };
    }

    private static function listUrl(string $key): ?string
    {
        return match ($key) {
            'about.idol' => self::aboutUrl(self::idolSlug()),
            'about.fansite' => self::aboutUrl(self::fanbaseSlug()),
            default => Route::has($key) ? route($key) : null,
        };
    }

    private static function aboutUrl(string $slug): string
    {
        return $slug !== '' ? route('about.show', $slug) : route('about.show');
    }

    private static function pageUrl(string $slug): ?string
    {
        $exists = CustomPage::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->exists();

        return $exists ? route('custom-pages.show', $slug) : null;
    }

    private static function aboutPath(string $slug): string
    {
        return $slug !== '' ? '/about/'.$slug : '/about';
    }

    private static function idolSlug(): string
    {
        $about = SettingBag::about();
        $slug = trim((string) ($about['idol_slug'] ?? ''));

        return $slug !== '' ? $slug : Str::slug((string) ($about['idol_name'] ?? ''));
    }

    private static function fanbaseSlug(): string
    {
        $about = SettingBag::about();
        $slug = trim((string) ($about['fanbase_slug'] ?? ''));

        return $slug !== '' ? $slug : Str::slug((string) ($about['fanbase_name'] ?? ''));
    }
}
