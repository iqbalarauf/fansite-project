<?php

namespace App\Models;

use App\Support\HeaderMenu;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'label', 'type', 'url', 'target', 'page_id', 'sort_order'])]
class MenuItem extends Model
{
    public const TYPE_LINK = 'link';

    public const TYPE_GROUP = 'group';

    public const TYPE_PAGE = 'page';

    public const TYPE_PAGE_LIST = 'page_list';

    public const TYPE_BLOG = 'blog';

    public const TYPE_NEWS = 'news';

    /**
     * @return array<int, string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_LINK,
            self::TYPE_GROUP,
            self::TYPE_PAGE,
            self::TYPE_PAGE_LIST,
            self::TYPE_BLOG,
            self::TYPE_NEWS,
        ];
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return BelongsTo<CustomPage, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(CustomPage::class, 'page_id');
    }

    /**
     * @param  Builder<MenuItem>  $query
     * @return Builder<MenuItem>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function resolvedUrl(): ?string
    {
        return match ($this->type) {
            self::TYPE_LINK => $this->url ?: null,
            self::TYPE_GROUP => null,
            self::TYPE_PAGE => $this->page && $this->page->status === 'published' ? route('custom-pages.show', $this->page->slug) : null,
            self::TYPE_PAGE_LIST => HeaderMenu::builtInUrl($this->target),
            self::TYPE_BLOG => route('blog.index'),
            self::TYPE_NEWS => route('news.index'),
            default => null,
        };
    }

    public function isExternal(): bool
    {
        return $this->type === self::TYPE_LINK
            && is_string($this->url)
            && str_starts_with($this->url, 'http');
    }
}
