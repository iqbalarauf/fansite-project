<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\GalleryPhoto;
use App\Models\Magazine;
use App\Models\NewsPost;
use App\Models\Post;
use App\Models\ShowTeater;
use App\Models\Trivia;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class WelcomePageData
{
    public function __construct(
        private EventTimeline $timeline,
        private CheckMemberLive $memberLive,
    ) {}

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
        $feedSource = SettingBag::welcomeFeedSource();
        $feedEnabled = SettingBag::featureEnabled($feedSource);

        $idolMemberName = (string) ($about['idol_shortname'] ?? '');
        $liveStatus = $this->memberLive->details($idolMemberName);

        $youtubeEnabled = filter_var($app['youtube_embed_enabled'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $youtubeModeValue = (string) ($app['youtube_display_mode'] ?? 'cards');
        $youtubeMode = in_array($youtubeModeValue, ['cards', 'embed'], true) ? $youtubeModeValue : 'cards';
        $youtubeUrl = (string) ($app['youtube_playlist_url'] ?? '');

        $idolSlug = trim((string) ($about['idol_slug'] ?? ''));
        $idolSlug = $idolSlug !== '' ? $idolSlug : Str::slug((string) ($about['idol_name'] ?? ''));

        $idolVersionValue = (string) ($about['idol_profile_version'] ?? 'jkt48');
        $idolProfileVersion = in_array($idolVersionValue, ['jkt48', 'general'], true) ? $idolVersionValue : 'jkt48';

        return [
            'idolName' => $about['idol_name'] ?? 'Oshimen',
            'idolSlug' => $idolSlug,
            'idolShortname' => $idolMemberName,
            'idolProfileVersion' => $idolProfileVersion,
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
            'heroButtons' => $this->heroButtons($app),
            'youtubeEnabled' => $youtubeEnabled,
            'youtubeDisplayMode' => $youtubeMode,
            'youtubePlaylistUrl' => $youtubeUrl,
            'youtubeEmbedUrl' => ($youtubeEnabled && $youtubeMode === 'embed') ? YoutubeEmbed::embedUrl($youtubeUrl) : null,
            'youtubeVideos' => ($youtubeEnabled && $youtubeMode === 'cards') ? YoutubePlaylist::videos($youtubeUrl, 7) : [],
            'sidebarName' => $app['sidebar_name'] ?? config('app.name', 'Laravel'),
            'appName' => $app['app_name'] ?? config('app.name', 'Laravel'),
            'showOnWelcome' => filter_var($about['idol_show_on_welcome'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'showCount' => $showCount,
            'setlistCount' => ShowTeater::query()->distinct()->count('setlist'),
            'discographyCount' => count(TextLines::parse($about['idol_discography'] ?? '')),
            'upcomingShowCount' => $upcomingShowCount,
            'lastEventDate' => $this->lastEventDate($today),
            'upcomingEvents' => $this->timeline->events('upcoming', null, null, $today, 5),
            'feedEnabled' => $feedEnabled,
            'feed' => $feedEnabled ? $this->feed($feedSource) : ['label' => '', 'heading' => '', 'indexRoute' => '#', 'items' => []],
            'galleryPhotos' => $this->galleryPhotos(),
            'showroomLive' => $liveStatus['showroom']['live'],
            'idnLive' => $liveStatus['idn']['live'],
            'showroomStreamUrl' => $liveStatus['showroom']['url'],
            'idnStreamUrl' => $liveStatus['idn']['url'],
        ];
    }

    /**
     * @param  array<string, mixed>  $app
     * @return array<int, array{label: string, url: string}>
     */
    private function heroButtons(array $app): array
    {
        $buttons = [];

        foreach ([1, 2] as $index) {
            if (! filter_var($app["hero_button_{$index}_enabled"] ?? 'true', FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $label = trim((string) ($app["hero_button_{$index}_label"] ?? ''));
            $type = (string) ($app["hero_button_{$index}_link_type"] ?? 'url');
            $value = (string) ($app["hero_button_{$index}_link_value"] ?? '');
            $url = HeroLink::resolve($type, $value);

            if ($label !== '' && $url !== null) {
                $buttons[] = ['label' => $label, 'url' => $url];
            }
        }

        return $buttons;
    }

    /**
     * @return Collection<int, GalleryPhoto>
     */
    private function galleryPhotos(): Collection
    {
        if (! in_array(SettingBag::galleryMode(), ['photos', 'both'], true)) {
            return collect();
        }

        return GalleryPhoto::query()->latest()->take(6)->get();
    }

    /**
     * @return array{label: string, heading: string, indexRoute: string, items: array<int, array<string, mixed>>}
     */
    private function feed(string $source): array
    {
        return match ($source) {
            'blog' => [
                'label' => 'Blog',
                'heading' => 'Blog Terbaru',
                'indexRoute' => route('blog.index'),
                'items' => $this->postFeed(BlogPost::class),
            ],
            'magazines' => [
                'label' => 'Majalah',
                'heading' => 'Majalah Terbaru',
                'indexRoute' => route('magazine.index'),
                'items' => Magazine::query()
                    ->latest('created_at')
                    ->take(4)
                    ->get()
                    ->map(fn (Magazine $magazine): array => [
                        'title' => $magazine->title,
                        'cover' => $magazine->cover,
                        'date' => $magazine->created_at,
                        'url' => route('magazine.show', $magazine->slug),
                    ])
                    ->all(),
            ],
            'trivia' => [
                'label' => 'Trivia',
                'heading' => 'Trivia Terbaru',
                'indexRoute' => route('trivia.index'),
                'items' => Trivia::query()
                    ->latest('created_at')
                    ->take(4)
                    ->get()
                    ->map(fn (Trivia $trivia): array => [
                        'title' => $trivia->title,
                        'cover' => $trivia->image,
                        'date' => $trivia->created_at,
                        'url' => route('trivia.index'),
                    ])
                    ->all(),
            ],
            default => [
                'label' => 'News',
                'heading' => 'Berita Terbaru',
                'indexRoute' => route('news.index'),
                'items' => $this->postFeed(NewsPost::class),
            ],
        };
    }

    /**
     * @param  class-string<Post>  $model
     * @return array<int, array<string, mixed>>
     */
    private function postFeed(string $model): array
    {
        return $model::query()
            ->published()
            ->latest('published_at')
            ->take(4)
            ->get()
            ->map(fn ($post): array => [
                'title' => $post->title,
                'cover' => $post->cover,
                'date' => $post->published_at ?? $post->created_at,
                'url' => $post->publicUrl(),
            ])
            ->all();
    }

    private function lastEventDate(string $today): ?string
    {
        $lastEvent = $this->timeline->events('past', null, null, $today, 1)->first();

        return $lastEvent['date'] ?? null;
    }
}
