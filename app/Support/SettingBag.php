<?php

namespace App\Support;

use App\Models\AboutSettings;
use App\Models\AppSettings;
use Illuminate\Support\Facades\Cache;

final class SettingBag
{
    /**
     * @return array<string, mixed>
     */
    public static function about(): array
    {
        return Cache::remember('about_settings', 3600, fn (): array => AboutSettings::query()->pluck('value', 'key')->all());
    }

    /**
     * @return array<string, mixed>
     */
    public static function app(): array
    {
        return Cache::remember('app_settings', 3600, fn (): array => AppSettings::query()->pluck('value', 'key')->all());
    }

    /**
     * Determine whether a content feature (e.g. "news", "blog") is enabled.
     * Defaults to enabled when the setting has never been saved.
     */
    public static function featureEnabled(string $feature): bool
    {
        return filter_var(self::app()[$feature.'_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Gallery display mode for the public page: photos, videos, or both.
     */
    public static function galleryMode(): string
    {
        $mode = (string) (self::app()['gallery_mode'] ?? 'photos');

        return in_array($mode, ['photos', 'videos', 'both'], true) ? $mode : 'photos';
    }

    /**
     * Content source for the welcome "Berita Terbaru" card: news, blog, magazines, or trivia.
     */
    public static function welcomeFeedSource(): string
    {
        $source = (string) (self::app()['welcome_feed_source'] ?? 'news');

        return in_array($source, ['news', 'blog', 'magazines', 'trivia'], true) ? $source : 'news';
    }

    /**
     * @param  array<string, mixed>  $about
     * @return array<int, array{photo: string|null, title: string, duration_from: string|null, duration_to: string|null}>
     */
    public static function kabeshaItems(array $about): array
    {
        $items = json_decode((string) ($about['kabesha_items'] ?? ''), true);

        if (is_array($items) && $items !== []) {
            return array_values(array_map(fn (array $item): array => [
                'photo' => filled($item['photo'] ?? null) ? (string) $item['photo'] : null,
                'title' => (string) ($item['title'] ?? ''),
                'duration_from' => ($item['duration_from'] ?? null) ?: null,
                'duration_to' => ($item['duration_to'] ?? null) ?: null,
            ], array_filter($items, 'is_array')));
        }

        $legacyPhotos = json_decode((string) ($about['kabesha_photos'] ?? ''), true);

        if (! is_array($legacyPhotos) || $legacyPhotos === []) {
            $legacyPhotos = filled($about['kabesha_photo'] ?? null) ? [(string) $about['kabesha_photo']] : [];
        }

        $legacyTitle = (string) ($about['kabesha_title'] ?? '');
        $legacyFrom = ($about['kabesha_duration_from'] ?? null) ?: null;
        $legacyTo = ($about['kabesha_duration_to'] ?? null) ?: null;

        return array_values(array_map(fn (string $path): array => [
            'photo' => $path,
            'title' => $legacyTitle,
            'duration_from' => $legacyFrom,
            'duration_to' => $legacyTo,
        ], array_values(array_filter(array_map('strval', is_array($legacyPhotos) ? $legacyPhotos : [])))));
    }

    /**
     * @param  array<string, mixed>  $about
     * @return array<int, array{photo: string|null, caption: string}>
     */
    public static function fanbaseGalleryItems(array $about): array
    {
        $items = json_decode((string) ($about['fanbase_gallery_items'] ?? ''), true);

        if (is_array($items) && $items !== []) {
            return array_slice(array_values(array_map(fn (array $item): array => [
                'photo' => filled($item['photo'] ?? null) ? (string) $item['photo'] : null,
                'caption' => (string) ($item['caption'] ?? ''),
            ], array_filter($items, 'is_array'))), 0, 20);
        }

        $legacy = json_decode((string) ($about['fanbase_gallery'] ?? '[]'), true);

        return array_slice(array_values(array_map(fn (string $path): array => [
            'photo' => $path,
            'caption' => '',
        ], array_values(array_filter(array_map('strval', is_array($legacy) ? $legacy : []))))), 0, 20);
    }

    /**
     * @param  array<string, mixed>  $about
     * @return array<int, array{photo: string|null, description: string}>
     */
    public static function fanbaseHistoryItems(array $about): array
    {
        $items = json_decode((string) ($about['fanbase_history_items'] ?? ''), true);

        if (! is_array($items) || $items === []) {
            return [];
        }

        return array_slice(array_values(array_map(fn (array $item): array => [
            'photo' => filled($item['photo'] ?? null) ? (string) $item['photo'] : null,
            'description' => (string) ($item['description'] ?? ''),
        ], array_filter($items, 'is_array'))), 0, 20);
    }
}
