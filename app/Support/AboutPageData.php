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
            'idolName' => $about['idol_name'] ?? 'Idol',
            'idolSlug' => $this->slug($about),
            'fanbaseLogo' => $about['fanbase_logo'] ?? null,
            'fanbaseDescription' => (string) ($about['fanbase_description'] ?? ''),
            'fanbaseActivities' => TextLines::parse($about['fanbase_activities'] ?? ''),
            'fanbaseGallery' => array_values(array_filter(is_array($gallery) ? $gallery : [])),
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
    private function slug(array $about): string
    {
        $slug = trim((string) ($about['idol_slug'] ?? ''));

        return $slug !== '' ? $slug : Str::slug((string) ($about['idol_name'] ?? ''));
    }
}
