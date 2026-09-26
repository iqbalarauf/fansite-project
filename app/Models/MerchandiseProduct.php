<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Database\Factories\MerchandiseProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MerchandiseProduct extends Model
{
    /** @use HasFactory<MerchandiseProductFactory> */
    use HasAuditColumns;

    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'images',
        'shop_url',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<MerchandiseProduct>  $query
     * @return Builder<MerchandiseProduct>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<MerchandiseProduct>  $query
     * @return Builder<MerchandiseProduct>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    /**
     * @return array<int, string>
     */
    public function imagePaths(): array
    {
        return array_values(array_filter(array_map('strval', is_array($this->images) ? $this->images : [])));
    }

    /**
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        return array_map(fn (string $path): string => Storage::url($path), $this->imagePaths());
    }

    public function coverUrl(): ?string
    {
        $paths = $this->imagePaths();

        return $paths === [] ? null : Storage::url($paths[0]);
    }
}
