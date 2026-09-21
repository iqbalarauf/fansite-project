<?php

namespace App\Models;

use App\Enums\ContentSection;
use App\Models\Concerns\HasAuditColumns;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class Post extends Model
{
    use HasAuditColumns;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover',
        'status',
        'is_featured',
        'published_at',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    abstract public function section(): ContentSection;

    /**
     * Sanitasi konten HTML saat disimpan (mencegah XSS).
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            set: static fn (?string $value): string => HtmlSanitizer::article($value),
        );
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $nested): void {
                $nested->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function isScheduled(): bool
    {
        return $this->status === 'published' && $this->published_at?->isFuture() === true;
    }

    public function displayStatus(): string
    {
        if ($this->isScheduled()) {
            return 'scheduled';
        }

        return $this->status;
    }

    public function publicUrl(): string
    {
        return route($this->section()->publicShowRoute(), $this->slug);
    }
}
