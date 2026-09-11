<?php

namespace App\Http\Controllers;

use App\Enums\ContentSection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPostController extends Controller
{
    public function index(Request $request): View
    {
        $contentSection = $this->section($request);
        $model = $contentSection->model();

        $featured = $model::query()
            ->published()
            ->with('category')
            ->where('is_featured', true)
            ->latest('published_at')
            ->first();

        $posts = $model::query()
            ->published()
            ->with('category')
            ->when($featured !== null, fn ($query) => $query->whereKeyNot($featured->id))
            ->latest('published_at')
            ->paginate(9);

        return view('posts.index', [
            'section' => $contentSection,
            'featured' => $featured,
            'posts' => $posts,
        ]);
    }

    public function show(Request $request): View
    {
        $contentSection = $this->section($request);
        $model = $contentSection->model();

        $article = $model::query()
            ->published()
            ->with('category')
            ->where('slug', (string) $request->route('post'))
            ->firstOrFail();

        $related = $model::query()
            ->published()
            ->whereKeyNot($article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('posts.show', [
            'section' => $contentSection,
            'post' => $article,
            'related' => $related,
        ]);
    }

    private function section(Request $request): ContentSection
    {
        return ContentSection::from((string) $request->route('section'));
    }
}
