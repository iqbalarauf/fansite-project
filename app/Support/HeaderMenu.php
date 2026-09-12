<?php

namespace App\Support;

use App\Models\MenuItem;
use Illuminate\Support\Collection;

final class HeaderMenu
{
    public const MODE_DEFAULT = 'default';

    public const MODE_CUSTOM = 'custom';

    public static function mode(): string
    {
        return (SettingBag::app()['header_menu_mode'] ?? self::MODE_DEFAULT) === self::MODE_CUSTOM
            ? self::MODE_CUSTOM
            : self::MODE_DEFAULT;
    }

    public static function isCustom(): bool
    {
        return self::mode() === self::MODE_CUSTOM;
    }

    /**
     * Pre-built pages that can be targeted by a "List Page" menu item.
     *
     * @return array<string, array{label: string, url: string}>
     */
    public static function builtInPages(): array
    {
        return [
            'home' => ['label' => 'Home', 'url' => route('home')],
            'about_idol' => ['label' => 'About Idol', 'url' => route('about.idol')],
            'about_fansite' => ['label' => 'About Fansite', 'url' => route('about.fansite')],
            'magazines' => ['label' => 'Majalah', 'url' => route('magazine.index')],
            'news' => ['label' => 'News', 'url' => route('news.index')],
            'blog' => ['label' => 'Blog', 'url' => route('blog.index')],
            'schedule' => ['label' => 'Schedule', 'url' => route('schedule.index')],
        ];
    }

    public static function builtInUrl(?string $key): ?string
    {
        $pages = self::builtInPages();

        return $key !== null && isset($pages[$key]) ? $pages[$key]['url'] : null;
    }

    public static function builtInLabel(?string $key): ?string
    {
        $pages = self::builtInPages();

        return $key !== null && isset($pages[$key]) ? $pages[$key]['label'] : null;
    }

    /**
     * Feature flag required by a built-in page target, if any.
     */
    public static function requiredFeature(?string $key): ?string
    {
        return match ($key) {
            'magazines' => 'magazines',
            'news' => 'news',
            'blog' => 'blog',
            default => null,
        };
    }

    public static function isItemAvailable(MenuItem $item): bool
    {
        if ($item->type === MenuItem::TYPE_NEWS) {
            return SettingBag::featureEnabled('news');
        }

        if ($item->type === MenuItem::TYPE_BLOG) {
            return SettingBag::featureEnabled('blog');
        }

        if ($item->type === MenuItem::TYPE_PAGE_LIST) {
            $feature = self::requiredFeature($item->target);

            return $feature === null || SettingBag::featureEnabled($feature);
        }

        return true;
    }

    /**
     * Build the nested menu tree used by the public header.
     *
     * @return array<int, array{id: int|string, label: string, url: ?string, external: bool, children: array<int, array<string, mixed>>}>
     */
    public static function tree(): array
    {
        return self::buildNodes(MenuItem::query()->with('page')->ordered()->get(), null);
    }

    /**
     * @param  Collection<int, MenuItem>  $items
     * @return array<int, array<string, mixed>>
     */
    private static function buildNodes(Collection $items, ?int $parentId): array
    {
        $nodes = [];

        foreach ($items->where('parent_id', $parentId) as $item) {
            if (! self::isItemAvailable($item)) {
                continue;
            }

            $children = self::buildNodes($items, $item->id);

            // Group items are containers only; hide them when they have no submenu.
            if ($item->type === MenuItem::TYPE_GROUP && $children === []) {
                continue;
            }

            $nodes[] = [
                'id' => $item->id,
                'label' => $item->label,
                'url' => $item->resolvedUrl(),
                'external' => $item->isExternal(),
                'children' => $children,
            ];
        }

        return $nodes;
    }
}
