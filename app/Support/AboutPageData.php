<?php

namespace App\Support;

use Illuminate\Support\Str;

final class AboutPageData
{
    public function __construct(private IdolTheaterStats $stats) {}

    /**
     * @return array<string, mixed>
     */
    public function idol(): array
    {
        $about = SettingBag::about();

        return [
            'idolName' => $about['idol_name'] ?? 'Oshimen',
            'idolSlug' => $this->slug($about),
            'idolPhoto' => $about['idol_photo'] ?? null,
            'idolDescription' => $about['idol_about'] ?? $about['idol_description'] ?? '',
            'idolAchievements' => TextLines::parse($about['idol_achievements'] ?? ''),
            'idolDiscography' => TextLines::parse($about['idol_discography'] ?? ''),
            'idolJikoshoukai' => (string) ($about['idol_jikoshoukai'] ?? ''),
            'kabeshaItems' => $this->kabeshaItems($about),
            'idolBirthDate' => ($about['idol_birth_date'] ?? null) ?: null,
            'idolBirthPlace' => (string) ($about['idol_birth_place'] ?? ''),
            'idolBloodType' => (string) ($about['idol_blood_type'] ?? ''),
            'idolHoroscope' => (string) ($about['idol_horoscope'] ?? ''),
            'idolInstagramUrl' => $about['idol_social_media_instagram'] ?? null,
            'idolTwitterUrl' => $about['idol_social_media_twitter'] ?? null,
            'idolTiktokUrl' => $about['idol_social_media_tiktok'] ?? null,
            'theater' => $this->stats->build(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function fansite(): array
    {
        $about = SettingBag::about();
        $gallery = json_decode((string) ($about['fanbase_gallery'] ?? '[]'), true);

        return [
            'fanbaseName' => $about['fanbase_name'] ?? 'Fanbase',
            'fanbaseSlug' => $this->fanbaseSlug($about),
            'idolName' => $about['idol_name'] ?? 'Idol',
            'idolSlug' => $this->slug($about),
            'fanbaseLogo' => $about['fanbase_logo'] ?? null,
            'fanbaseDescription' => (string) ($about['fanbase_description'] ?? ''),
            'fanbaseStructure' => TextLines::parse($about['fanbase_structure'] ?? ''),
            'fanbaseActivities' => TextLines::parse($about['fanbase_activities'] ?? ''),
            'fanbaseGallery' => array_values(array_filter(is_array($gallery) ? $gallery : [])),
            'fanbaseGalleryItems' => $this->fanbaseGalleryItems($about),
            'fanbaseCtaEnabled' => filter_var($about['fanbase_cta_enabled'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'fanbaseCtaBackground' => $about['fanbase_cta_background'] ?? null,
            'fanbaseCtaTitle' => (string) ($about['fanbase_cta_title'] ?? ''),
            'fanbaseCtaButton1Text' => (string) ($about['fanbase_cta_button1_text'] ?? ''),
            'fanbaseCtaButton1Link' => (string) ($about['fanbase_cta_button1_link'] ?? ''),
            'fanbaseCtaButton2Text' => (string) ($about['fanbase_cta_button2_text'] ?? ''),
            'fanbaseCtaButton2Link' => (string) ($about['fanbase_cta_button2_link'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $about
     */
    private function fanbaseSlug(array $about): string
    {
        $slug = trim((string) ($about['fanbase_slug'] ?? ''));

        return $slug !== '' ? $slug : Str::slug((string) ($about['fanbase_name'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $about
     * @return array<int, array{photo: string, caption: string}>
     */
    private function fanbaseGalleryItems(array $about): array
    {
        $items = json_decode((string) ($about['fanbase_gallery_items'] ?? ''), true);

        if (is_array($items) && $items !== []) {
            return array_slice(array_values(array_map(fn (array $item): array => [
                'photo' => (string) ($item['photo'] ?? ''),
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
     */
    private function slug(array $about): string
    {
        $slug = trim((string) ($about['idol_slug'] ?? ''));

        return $slug !== '' ? $slug : Str::slug((string) ($about['idol_name'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $about
     * @return array<int, array{photo: string|null, title: string, duration_from: string|null, duration_to: string|null}>
     */
    private function kabeshaItems(array $about): array
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
}
