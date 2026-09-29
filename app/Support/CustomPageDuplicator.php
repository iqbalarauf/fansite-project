<?php

namespace App\Support;

use App\Models\CustomPage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Duplicates a custom page (and its uploaded files) into a new draft page.
 */
class CustomPageDuplicator
{
    public function duplicate(CustomPage $page): CustomPage
    {
        $blocks = $this->duplicateBlocks($page->blocks ?? []);

        $heroImage = filled($page->hero_image)
            ? $this->duplicateStoredFile($page->hero_image)
            : null;

        $title = $this->duplicateTitle($page->title);

        return CustomPage::query()->create([
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'status' => 'draft',
            'display_mode' => $page->display_mode ?? 'full',
            'background_color' => $page->background_color ?? 'slate',
            'title_alignment' => $page->title_alignment ?? 'left',
            'hero_enabled' => (bool) ($page->hero_enabled ?? false),
            'hero_image' => $heroImage,
            'blocks' => array_values($blocks),
        ]);
    }

    /**
     * Deep clone block trees, regenerating ids and copying uploaded image files
     * so the duplicate owns its files and cannot delete the original's.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public function duplicateBlocks(array $blocks): array
    {
        return array_map(fn (array $block): array => $this->duplicateBlock($block), $blocks);
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public function duplicateBlock(array $block): array
    {
        $block['id'] = (string) Str::uuid();

        if (($block['type'] ?? null) === 'container') {
            $columns = [];

            foreach (($block['data']['columns'] ?? []) as $column) {
                $nested = [];

                foreach (($column['blocks'] ?? []) as $nestedBlock) {
                    $nested[] = $this->duplicateBlock($nestedBlock);
                }

                $columns[] = [
                    'id' => (string) Str::uuid(),
                    'blocks' => $nested,
                ];
            }

            $block['data']['columns'] = $columns;

            return $block;
        }

        if (($block['type'] ?? null) === 'image' && filled($block['data']['storage_path'] ?? null)) {
            $block['data']['storage_path'] = $this->duplicateStoredFile($block['data']['storage_path']);
        }

        if (($block['type'] ?? null) === 'gallery') {
            $images = [];

            foreach (($block['data']['images'] ?? []) as $image) {
                if (filled($image['storage_path'] ?? null)) {
                    $image['storage_path'] = $this->duplicateStoredFile($image['storage_path']);
                }

                $images[] = $image;
            }

            $block['data']['images'] = $images;
        }

        return $block;
    }

    public function duplicateStoredFile(?string $path): ?string
    {
        if (blank($path) || ! Storage::disk('public')->exists($path)) {
            return $path;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $clonePath = ($directory !== '.' ? $directory.'/' : '').Str::uuid().($extension !== '' ? '.'.$extension : '');

        Storage::disk('public')->copy($path, $clonePath);

        return $clonePath;
    }

    public function duplicateTitle(string $title): string
    {
        $base = preg_replace('/\s*\(Copy(?: \d+)?\)$/i', '', $title) ?: $title;

        return $base.' (Copy)';
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'page';
        $slug = $baseSlug;
        $suffix = 2;

        while (CustomPage::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
