<?php

namespace App\Http\Controllers\Content;

use App\Enums\ContentSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Support\ListingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $contentSection = $this->section($request);

        $filters = ListingQuery::from($request, ['title', 'published_at', 'created_at'], 'created_at', [
            'status' => '',
            'category' => '',
        ]);

        $query = $contentSection->model()::query()->with('category');

        $query
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('slug', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['category'] !== '', fn ($query) => $query->where('category_id', $filters['category']));

        $posts = $query
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('content.posts.index', [
            'section' => $contentSection,
            'posts' => $posts,
            'filters' => $filters,
            'categories' => Category::query()->ofType($contentSection)->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $contentSection = $this->section($request);

        return view('content.posts.form', [
            'section' => $contentSection,
            'post' => null,
            'categories' => Category::query()->ofType($contentSection)->orderBy('name')->get(),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $contentSection = $this->section($request);
        $model = $contentSection->model();

        $post = new $model;
        $this->fillPost($post, $request);
        $post->save();

        return redirect()->route($contentSection->adminRoute().'.index')
            ->with('success', $contentSection->label().' berhasil ditambahkan.');
    }

    public function edit(Request $request): View
    {
        $contentSection = $this->section($request);
        $post = $contentSection->model()::query()->findOrFail($this->postId($request));

        return view('content.posts.form', [
            'section' => $contentSection,
            'post' => $post,
            'categories' => Category::query()->ofType($contentSection)->orderBy('name')->get(),
        ]);
    }

    public function update(PostRequest $request): RedirectResponse
    {
        $contentSection = $this->section($request);

        $post = $contentSection->model()::query()->findOrFail($this->postId($request));
        $this->fillPost($post, $request);
        $post->save();

        return redirect()->route($contentSection->adminRoute().'.index')
            ->with('success', $contentSection->label().' berhasil diupdate.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $contentSection = $this->section($request);

        $contentSection->model()::query()->findOrFail($this->postId($request))->delete();

        return redirect()->route($contentSection->adminRoute().'.index')
            ->with('success', $contentSection->label().' berhasil dihapus.');
    }

    private function section(Request $request): ContentSection
    {
        return ContentSection::from((string) $request->route('section'));
    }

    private function postId(Request $request): int
    {
        return (int) $request->route('id');
    }

    private function fillPost(Post $post, PostRequest $request): void
    {
        $validated = $request->validated();

        $post->fill([
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'] ?? null,
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $this->resolvePublishedAt($validated),
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        if (isset($validated['cover'])) {
            if ($post->cover) {
                Storage::disk('public')->delete($post->cover);
            }

            $post->cover = $validated['cover']->store('content/covers', 'public');
        }

        if (isset($validated['og_image'])) {
            if ($post->og_image) {
                Storage::disk('public')->delete($post->og_image);
            }

            $post->og_image = $validated['og_image']->store('content/og', 'public');
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolvePublishedAt(array $validated): ?string
    {
        $publishedAt = $validated['published_at'] ?? null;

        if ($validated['status'] === 'published' && $publishedAt === null) {
            return now()->toDateTimeString();
        }

        return $publishedAt;
    }
}
