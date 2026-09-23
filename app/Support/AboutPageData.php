<?php

namespace App\Support;

use App\Models\CustomPage;
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

        $versionValue = (string) ($about['idol_profile_version'] ?? 'jkt48');
        $version = in_array($versionValue, ['jkt48', 'general'], true) ? $versionValue : 'jkt48';
        $kabeshaEnabled = filter_var($about['kabesha_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $kabeshaDefaultTitle = (string) ($about['kabesha_default_title'] ?? '');
        $kabeshaItems = $this->kabeshaItems($about, $kabeshaDefaultTitle);

        return [
            'idolName' => $about['idol_name'] ?? 'Oshimen',
            'idolSlug' => $this->slug($about),
            'idolProfileVersion' => $version,
            'idolTerm' => $version === 'jkt48' ? 'Oshimen' : 'Idol/Bias',
            'idolPhoto' => $about['idol_photo'] ?? null,
            'idolDescription' => $about['idol_about'] ?? $about['idol_description'] ?? '',
            'idolAchievements' => TextLines::parse($about['idol_achievements'] ?? ''),
            'idolDiscography' => TextLines::parse($about['idol_discography'] ?? ''),
            'idolJikoshoukai' => (string) ($about['idol_jikoshoukai'] ?? ''),
            'kabeshaEnabled' => $kabeshaEnabled,
            'kabeshaDefaultTitle' => $kabeshaDefaultTitle,
            'kabeshaItems' => $kabeshaItems,
            'idolBirthDate' => ($about['idol_birth_date'] ?? null) ?: null,
            'idolBirthPlace' => (string) ($about['idol_birth_place'] ?? ''),
            'idolBloodType' => (string) ($about['idol_blood_type'] ?? ''),
            'idolHoroscope' => (string) ($about['idol_horoscope'] ?? ''),
            'idolInstagramUrl' => $about['idol_social_media_instagram'] ?? null,
            'idolTwitterUrl' => $about['idol_social_media_twitter'] ?? null,
            'idolTiktokUrl' => $about['idol_social_media_tiktok'] ?? null,
            'theater' => $this->stats->build(),
            'showTeaterEnabled' => filter_var($about['idol_show_teater_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
            'unitSongEnabled' => filter_var($about['idol_unit_song_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
            'centersEnabled' => filter_var($about['idol_centers_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Kabesha items with the configured default title applied to untitled photos.
     *
     * @param  array<string, mixed>  $about
     * @return array<int, array{photo: string|null, title: string, duration_from: string|null, duration_to: string|null}>
     */
    private function kabeshaItems(array $about, string $defaultTitle): array
    {
        $items = SettingBag::kabeshaItems($about);

        if ($defaultTitle === '') {
            return $items;
        }

        return array_map(function (array $item) use ($defaultTitle): array {
            if (trim((string) ($item['title'] ?? '')) === '') {
                $item['title'] = $defaultTitle;
            }

            return $item;
        }, $items);
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
            'fanbaseStructureEnabled' => filter_var($about['fanbase_structure_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
            'fanbaseStructure' => TextLines::parse($about['fanbase_structure'] ?? ''),
            'fanbaseActivitiesEnabled' => filter_var($about['fanbase_activities_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
            'fanbaseActivities' => TextLines::parse($about['fanbase_activities'] ?? ''),
            'fanbaseGallery' => array_values(array_filter(is_array($gallery) ? $gallery : [])),
            'fanbaseGalleryItems' => SettingBag::fanbaseGalleryItems($about),
            'fanbaseCtaEnabled' => filter_var($about['fanbase_cta_enabled'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'fanbaseCtaBackground' => $about['fanbase_cta_background'] ?? null,
            'fanbaseCtaTitle' => (string) ($about['fanbase_cta_title'] ?? ''),
            'fanbaseCtaButton1Text' => (string) ($about['fanbase_cta_button1_text'] ?? ''),
            'fanbaseCtaButton1Link' => (string) ($about['fanbase_cta_button1_link'] ?? ''),
            'fanbaseCtaButton2Text' => (string) ($about['fanbase_cta_button2_text'] ?? ''),
            'fanbaseCtaButton2Link' => (string) ($about['fanbase_cta_button2_link'] ?? ''),
            'fanbaseHistoryEnabled' => filter_var($about['fanbase_history_enabled'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'fanbaseHistorySource' => $this->historySource($about),
            'fanbaseHistoryCustomPage' => $this->historyPage($about),
            'fanbaseHistoryItems' => SettingBag::fanbaseHistoryItems($about),
        ];
    }

    /**
     * @param  array<string, mixed>  $about
     */
    private function historySource(array $about): string
    {
        $source = (string) ($about['fanbase_history_source'] ?? 'default');

        return in_array($source, ['custom', 'default'], true) ? $source : 'default';
    }

    /**
     * @param  array<string, mixed>  $about
     * @return array{title: string, slug: string}|null
     */
    private function historyPage(array $about): ?array
    {
        $pageId = (int) ($about['fanbase_history_custom_page_id'] ?? 0);

        if ($pageId <= 0) {
            return null;
        }

        $page = CustomPage::query()->find($pageId);

        if ($page === null) {
            return null;
        }

        return ['title' => (string) $page->title, 'slug' => (string) $page->slug];
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
     */
    private function slug(array $about): string
    {
        $slug = trim((string) ($about['idol_slug'] ?? ''));

        return $slug !== '' ? $slug : Str::slug((string) ($about['idol_name'] ?? ''));
    }
}
