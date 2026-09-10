<?php

namespace App\Support;

use App\Models\ShowTeater;

final class WelcomePageData
{
    public function __construct(private EventTimeline $timeline) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $about = SettingBag::about();
        $app = SettingBag::app();
        $today = now()->toDateString();
        $showCount = ShowTeater::query()->count();
        $upcomingShowCount = $this->timeline->upcomingShowCount($today);

        return [
            'idolName' => $about['idol_name'] ?? 'Oshimen',
            'idolDescription' => $about['idol_about'] ?? $about['idol_description'] ?? 'This is your idol profile.',
            'idolPhoto' => $about['idol_photo'] ?? null,
            'instagramUrl' => $about['instagram_url'] ?? $about['idol_social_media_instagram'] ?? null,
            'twitterUrl' => $about['twitter_url'] ?? $about['idol_social_media_twitter'] ?? null,
            'tiktokUrl' => $about['tiktok_url'] ?? $about['idol_social_media_tiktok'] ?? null,
            'idolInstagramUrl' => $about['idol_social_media_instagram'] ?? null,
            'idolTwitterUrl' => $about['idol_social_media_twitter'] ?? null,
            'idolTiktokUrl' => $about['idol_social_media_tiktok'] ?? null,
            'heroImage' => $app['hero_image'] ?? null,
            'appLogo' => $app['app_logo'] ?? null,
            'sidebarName' => $app['sidebar_name'] ?? config('app.name', 'Laravel'),
            'appName' => $app['app_name'] ?? config('app.name', 'Laravel'),
            'showOnWelcome' => filter_var($about['idol_show_on_welcome'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'showCount' => $showCount,
            'setlistCount' => ShowTeater::query()->distinct()->count('setlist'),
            'upcomingShowCount' => $upcomingShowCount,
            'lastEventDate' => $this->lastEventDate($today),
            'upcomingEvents' => $this->timeline->events('upcoming', null, null, $today, 5),
        ];
    }

    private function lastEventDate(string $today): ?string
    {
        $lastEvent = $this->timeline->events('past', null, null, $today, 1)->first();

        return $lastEvent['date'] ?? null;
    }
}
