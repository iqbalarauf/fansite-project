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
        $liveStatus = $this->memberLive->status($idolMemberName);

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
            'feedEnabled' => $feedEnabled,
            'feed' => $feedEnabled ? $this->feed($feedSource) : ['label' => '', 'heading' => '', 'indexRoute' => '#', 'items' => []],
            'galleryPhotos' => $this->galleryPhotos(),
            'showroomLive' => $liveStatus['showroom'],
            'idnLive' => $liveStatus['idn'],
        ];
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
