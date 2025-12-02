<?php

namespace App\Http\Controllers;

use App\Models\CustomPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CustomPageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['show']);
    }

    /**
     * Show pages owned by the authenticated user (management list).
     */
    public function index()
    {
        $pages = CustomPage::where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->through(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'status' => $p->status,
                'published_at' => $p->published_at,
            ]);

        return Inertia::render('Pages/Manage', [
            'pages' => $pages,
        ]);
    }

    public function show(CustomPage $page)
    {
        if ($page->status !== 'published' && auth()->id() !== $page->user_id) {
            abort(404);
        }

        return Inertia::render('Pages/Edit', [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'hero_image' => $page->hero_image ? Storage::url($page->hero_image) : null,
                'body' => $page->body,
                'has_cta_section' => $page->has_cta_section,
                'cta_bg_image' => $page->cta_bg_image ? Storage::url($page->cta_bg_image) : null,
                'cta_title' => $page->cta_title,
                'cta_description' => $page->cta_description,
                'cta_button_text' => $page->cta_button_text,
                'cta_button_url' => $page->cta_button_url,
                'status' => $page->status,
                'published_at' => $page->published_at,
                'show_in_menu' => $page->show_in_menu,
                'menu_order' => $page->menu_order,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Pages/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'hero_image' => 'nullable|image|max:5120',
            'body' => 'nullable|string',
            'has_cta_section' => 'boolean',
            'cta_bg_image' => 'nullable|image|max:5120',
            'cta_title' => 'nullable|string|max:255',
            'cta_description' => 'nullable|string',
            'cta_button_text' => 'nullable|string|max:100',
            'cta_button_url' => 'nullable|url|max:500',
            'status' => 'required|in:draft,published',
            'show_in_menu' => 'boolean',
            'menu_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('pages/hero', 'public');
        }

        if ($request->hasFile('cta_bg_image')) {
            $data['cta_bg_image'] = $request->file('cta_bg_image')->store('pages/cta', 'public');
        }

        if ($data['status'] === 'published' && !isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        $page = CustomPage::create($data);

        return redirect()->route('pages.index')->with('flash', ['banner' => 'Page created.', 'bannerStyle' => 'success']);
    }

    public function edit(CustomPage $page)
    {
        $this->authorize('update', $page);

        return Inertia::render('Pages/Edit', [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'hero_image' => $page->hero_image ? Storage::url($page->hero_image) : null,
                'body' => $page->body,
                'has_cta_section' => $page->has_cta_section,
                'cta_bg_image' => $page->cta_bg_image ? Storage::url($page->cta_bg_image) : null,
                'cta_title' => $page->cta_title,
                'cta_description' => $page->cta_description,
                'cta_button_text' => $page->cta_button_text,
                'cta_button_url' => $page->cta_button_url,
                'status' => $page->status,
                'published_at' => $page->published_at,
                'show_in_menu' => $page->show_in_menu,
                'menu_order' => $page->menu_order,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'meta_keywords' => $page->meta_keywords,
            ],
        ]);
    }

    public function update(Request $request, CustomPage $page): RedirectResponse
    {
        $this->authorize('update', $page);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'hero_image' => 'nullable|image|max:5120',
            'body' => 'nullable|string',
            'has_cta_section' => 'boolean',
            'cta_bg_image' => 'nullable|image|max:5120',
            'cta_title' => 'nullable|string|max:255',
            'cta_description' => 'nullable|string',
            'cta_button_text' => 'nullable|string|max:100',
            'cta_button_url' => 'nullable|url|max:500',
            'status' => 'required|in:draft,published',
            'show_in_menu' => 'boolean',
            'menu_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('hero_image')) {
            $newPath = $request->file('hero_image')->store('pages/hero', 'public');
            if ($newPath) {
                if ($page->hero_image) {
                    Storage::disk('public')->delete($page->hero_image);
                }
                $data['hero_image'] = $newPath;
            }
        }

        if ($request->hasFile('cta_bg_image')) {
            $newPath = $request->file('cta_bg_image')->store('pages/cta', 'public');
            if ($newPath) {
                if ($page->cta_bg_image) {
                    Storage::disk('public')->delete($page->cta_bg_image);
                }
                $data['cta_bg_image'] = $newPath;
            }
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $page->update($data);

        return redirect()->route('pages.edit', $page)->with('flash', ['banner' => 'Page updated.', 'bannerStyle' => 'success']);
    }

    public function destroy(CustomPage $page): RedirectResponse
    {
        $this->authorize('delete', $page);

        if ($page->hero_image) {
            Storage::disk('public')->delete($page->hero_image);
        }
        if ($page->cta_bg_image) {
            Storage::disk('public')->delete($page->cta_bg_image);
        }

        $page->delete();

        return redirect()->route('pages.index')->with('flash', ['banner' => 'Page deleted.', 'bannerStyle' => 'success']);
    }
}
