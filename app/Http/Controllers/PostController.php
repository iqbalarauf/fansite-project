<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index','show']);
    }

    /**
     * Show posts owned by the authenticated user (management list).
     */
    public function manage()
    {
        $posts = Post::where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->paginate(20);

        return Inertia::render('Posts/Manage', [
            'posts' => $posts,
        ]);
    }

    public function index()
    {
        $posts = Post::where('status','published')
            ->whereNotNull('published_at')
            ->where('published_at','<=',now())
            ->orderByDesc('published_at')
            ->paginate(10);

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(Post $post)
    {
        if ($post->status !== 'published' && auth()->id() !== $post->user_id) {
            abort(404);
        }

        return Inertia::render('Blog/Show', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'body_html' => $post->body_html,
                'featured_image' => $post->featured_image ? Storage::url($post->featured_image) : null,
                'category' => $post->category,
                'tags' => $post->tags,
                'status' => $post->status,
                'published_at' => $post->published_at,
                'user' => [ 'id' => $post->user->id, 'name' => $post->user->name ],
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
                'meta_keywords' => $post->meta_keywords,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Posts/Create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('posts','public');
        }

        // normalize tags: accept comma-separated string or array
        if (!empty($data['tags'])) {
            if (is_string($data['tags'])) {
                $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags']))));
            } elseif (is_array($data['tags'])) {
                $data['tags'] = array_values(array_filter($data['tags']));
            }
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = Post::create($data);

        // Redirect to the authenticated user's posts management page so the
        // server-side flash (Banner) appears on the destination immediately.
        return redirect()->route('posts.manage')->with('flash', ['banner' => 'Post created.', 'bannerStyle' => 'success']);
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        return Inertia::render('Posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'featured_image' => $post->featured_image ? Storage::url($post->featured_image) : null,
                'category' => $post->category,
                'tags' => $post->tags,
                'status' => $post->status,
                'published_at' => $post->published_at,
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
                'meta_keywords' => $post->meta_keywords,
            ],
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            // Store the new image first. Only if storage succeeds do we remove the
            // old file. This prevents situations where we delete the old image and
            // then fail to save the new one, leaving the post without any image.
            $newPath = $request->file('featured_image')->store('posts', 'public');
            if ($newPath) {
                // delete previous file if one exists
                if ($post->featured_image) {
                    Storage::disk('public')->delete($post->featured_image);
                }
                $data['featured_image'] = $newPath;
            }
        }

        // normalize tags: accept comma-separated string or array
        if (!empty($data['tags'])) {
            if (is_string($data['tags'])) {
                $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags']))));
            } elseif (is_array($data['tags'])) {
                $data['tags'] = array_values(array_filter($data['tags']));
            }
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post->update($data);

        // After updating, redirect back to the Edit page so the user sees the
        // newly uploaded image (and page state) immediately.
        return redirect()->route('posts.edit', $post)->with('flash', ['banner' => 'Post updated.', 'bannerStyle' => 'success']);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        $post->delete();
        // redirect to posts.manage so the Banner flash shows in the management UI
        return redirect()->route('posts.manage')->with('flash', ['banner' => 'Post deleted.', 'bannerStyle' => 'success']);
    }
}
