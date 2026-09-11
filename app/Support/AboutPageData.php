<?php

namespace App\Support;

final class AboutPageData
{
    /**
     * @return array<string, mixed>
     */
    public function idol(): array
    {
        $about = SettingBag::about();

        return [
            'idolName' => $about['idol_name'] ?? 'Oshimen',
            'idolPhoto' => $about['idol_photo'] ?? null,
            'idolDescription' => $about['idol_about'] ?? $about['idol_description'] ?? '',
            'idolAchievements' => $this->lines($about['idol_achievements'] ?? ''),
            'idolDiscography' => $this->lines($about['idol_discography'] ?? ''),
            'idolJikoshoukai' => (string) ($about['idol_jikoshoukai'] ?? ''),
            'idolBirthDate' => ($about['idol_birth_date'] ?? null) ?: null,
            'idolBirthPlace' => (string) ($about['idol_birth_place'] ?? ''),
            'idolBloodType' => (string) ($about['idol_blood_type'] ?? ''),
            'idolHoroscope' => (string) ($about['idol_horoscope'] ?? ''),
            'idolInstagramUrl' => $about['idol_social_media_instagram'] ?? null,
            'idolTwitterUrl' => $about['idol_social_media_twitter'] ?? null,
            'idolTiktokUrl' => $about['idol_social_media_tiktok'] ?? null,
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
            'fanbaseLogo' => $about['fanbase_logo'] ?? null,
            'fanbaseDescription' => (string) ($about['fanbase_description'] ?? ''),
            'fanbaseActivities' => $this->lines($about['fanbase_activities'] ?? ''),
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
     * Split a multi-line textarea value into trimmed non-empty lines.
     *
     * @return list<string>
     */
    private function lines(mixed $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter(fn (string $line): bool => $line !== '')
            ->values()
            ->all();
    }
}
