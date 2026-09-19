<?php

namespace App\Http\Controllers;

use App\Enums\ContentSection;
use App\Models\ConcertEvents;
use App\Models\CustomPage;
use App\Models\LiveStreaming;
use App\Models\Magazine;
use App\Models\MeetGreetEvents;
use App\Models\Post;
use App\Models\ShowTeater;
use App\Models\User;
use App\Support\SettingBag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSearchController extends Controller
{
    private const LIMIT = 5;

    private const MIN_QUERY_LENGTH = 2;

    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->string('q')->toString());

        if (mb_strlen($query) < self::MIN_QUERY_LENGTH) {
            return response()->json(['groups' => []]);
        }

        $user = $request->user();
        $groups = [];

        $navigation = $this->navigation($user, $query);

        if ($navigation !== []) {
            $groups[] = ['label' => __('Menu'), 'items' => $navigation];
        }

        if ($user->canAccessMasterData()) {
            $this->appendGroup($groups, __('Master Data'), array_merge(
                $this->showTeater($query),
                $this->liveStreaming($query),
                $this->concertEvents($query),
                $this->meetGreetEvents($query),
            ));
        }

        if ($user->canAccessPages()) {
            $this->appendGroup($groups, __('Konten'), array_merge(
                $this->customPages($query),
                $this->posts($query),
                $this->magazines($query),
            ));
        }

        if ($user->isSuperAdmin()) {
            $this->appendGroup($groups, __('User'), $this->users($query));
        }

        return response()->json(['groups' => $groups]);
    }

    /**
     * @param  array<int, array{label: string, items: array<int, array<string, string>>}>  $groups
     * @param  array<int, array<string, string>>  $items
     */
    private function appendGroup(array &$groups, string $label, array $items): void
    {
        if ($items !== []) {
            $groups[] = ['label' => $label, 'items' => $items];
        }
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function navigation(User $user, string $query): array
    {
        $items = [];

        if ($user->canAccessMasterData()) {
            $items[] = ['label' => __('Statistik Oshimen'), 'url' => route('dashboard')];
            $items[] = ['label' => __('Show Teater'), 'url' => route('show-teater.index')];
            $items[] = ['label' => __('Setlist & Unit Song'), 'url' => route('show-teater.categories.index')];
            $items[] = ['label' => __('Meet & Greet Events'), 'url' => route('meet-greet-events.index')];
            $items[] = ['label' => __('Concert & Events'), 'url' => route('concert-events.index')];
            $items[] = ['label' => __('Live Streaming'), 'url' => route('live-streaming.index')];
        }

        if ($user->canAccessPages()) {
            $items[] = ['label' => __('Pages'), 'url' => route('pages.index')];

            if (SettingBag::featureEnabled('magazines')) {
                $items[] = ['label' => __('Majalah'), 'url' => route('magazines.index')];
            }

            $items[] = ['label' => __('Galeri'), 'url' => route('content.gallery.index')];

            if (SettingBag::featureEnabled('news')) {
                $items[] = ['label' => __('News'), 'url' => route('content.news.index')];
            }

            if (SettingBag::featureEnabled('blog')) {
                $items[] = ['label' => __('Blog'), 'url' => route('content.blog.index')];
            }

            $items[] = ['label' => __('Timeline'), 'url' => route('content.timeline.index')];

            if (SettingBag::featureEnabled('trivia')) {
                $items[] = ['label' => __('Trivia'), 'url' => route('content.trivia.index')];
            }

            if ($user->isSuperAdmin()) {
                $items[] = ['label' => __('Photobooth'), 'url' => route('photobooth.edit')];
            }
        }

        if ($user->isSuperAdmin()) {
            $items[] = ['label' => __('Daftar User'), 'url' => route('users.index')];
        }

        return $this->filter($items, $query);
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function showTeater(string $query): array
    {
        return ShowTeater::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('setlist', 'like', "%{$query}%")
                    ->orWhere('unit_song', 'like', "%{$query}%")
                    ->orWhere('show_date', 'like', "%{$query}%")
                    ->orWhere('show_id', 'like', "%{$query}%");
            })
            ->orderByDesc('show_date')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (ShowTeater $show): array => [
                'label' => $show->setlist,
                'description' => trim('#'.$show->show_id.' · '.$show->show_date.($show->unit_song ? ' · '.$show->unit_song : '')),
                'url' => route('show-teater.index', ['search' => $query]),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function liveStreaming(string $query): array
    {
        return LiveStreaming::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('platform', 'like', "%{$query}%")
                    ->orWhere('additional_info', 'like', "%{$query}%")
                    ->orWhere('live_id', 'like', "%{$query}%");
            })
            ->orderByDesc('live_date')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (LiveStreaming $live): array => [
                'label' => $live->platform,
                'description' => trim(($live->live_date?->format('d M Y') ?? '').($live->additional_info ? ' · '.$live->additional_info : '')),
                'url' => route('live-streaming.index', ['search' => $query]),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function concertEvents(string $query): array
    {
        return ConcertEvents::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('event_name', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%")
                    ->orWhere('status', 'like', "%{$query}%");
            })
            ->orderByDesc('event_date')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (ConcertEvents $event): array => [
                'label' => $event->event_name,
                'description' => trim(($event->event_date?->format('d M Y') ?? '').($event->location ? ' · '.$event->location : '')),
                'url' => route('concert-events.index', ['search' => $query]),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function meetGreetEvents(string $query): array
    {
        return MeetGreetEvents::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('event_name', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%");
            })
            ->orderByDesc('event_date')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (MeetGreetEvents $event): array => [
                'label' => $event->event_name,
                'description' => trim(($event->event_date?->format('d M Y') ?? '').($event->location ? ' · '.$event->location : '')),
                'url' => route('meet-greet-events.index', ['search' => $query]),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function customPages(string $query): array
    {
        return CustomPage::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%");
            })
            ->orderByDesc('updated_at')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (CustomPage $page): array => [
                'label' => $page->title,
                'description' => trim('/'.$page->slug.' · '.$page->status),
                'url' => route('pages.edit', $page),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function posts(string $query): array
    {
        $items = [];

        foreach (ContentSection::cases() as $section) {
            if (! SettingBag::featureEnabled($section->value)) {
                continue;
            }

            $model = $section->model();

            $results = $model::query()
                ->where(function ($builder) use ($query): void {
                    $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%");
                })
                ->orderByDesc('updated_at')
                ->limit(self::LIMIT)
                ->get();

            foreach ($results as $post) {
                /** @var Post $post */
                $items[] = [
                    'label' => $post->title,
                    'description' => $section->label().' · '.($post->published_at?->format('d M Y') ?? __('Draft')),
                    'url' => route($section->adminRoute().'.edit', $post->getKey()),
                ];
            }
        }

        return $items;
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function magazines(string $query): array
    {
        if (! SettingBag::featureEnabled('magazines')) {
            return [];
        }

        return Magazine::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%");
            })
            ->orderByDesc('created_at')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Magazine $magazine): array => [
                'label' => $magazine->title,
                'description' => __('Majalah').' · '.$magazine->created_at?->format('d M Y'),
                'url' => route('magazines.index', ['search' => $query]),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function users(string $query): array
    {
        return User::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (User $user): array => [
                'label' => $user->name,
                'description' => $user->email,
                'url' => route('users.index', ['search' => $query]),
            ])
            ->all();
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $items
     * @return array<int, array{label: string, description: string, url: string}>
     */
    private function filter(array $items, string $query): array
    {
        $needle = Str::lower($query);

        return array_values(array_map(
            static fn (array $item): array => [
                'label' => $item['label'],
                'description' => __('Buka halaman'),
                'url' => $item['url'],
            ],
            array_filter(
                $items,
                static fn (array $item): bool => Str::contains(Str::lower($item['label']), $needle),
            ),
        ));
    }
}
