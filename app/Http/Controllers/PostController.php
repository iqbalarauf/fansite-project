<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        return view('blog.manage', compact('posts'));
    }

    public function index()
    {
        $posts = Post::where('status','published')
            ->whereNotNull('published_at')
            ->where('published_at','<=',now())
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('blog.index', compact('posts'));
    }

    public function show(Post $post)
    {
        if ($post->status !== 'published' && auth()->id() !== $post->user_id) {
            abort(404);
        }

        return view('blog.show', compact('post'));
    }

    public function create()
    {
        return view('blog.create');
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

        return redirect()->route('blog.show', $post->slug)->with('success','Post created.');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        return view('blog.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            // delete old if exists
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
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

        $post->update($data);

        return redirect()->route('blog.show', $post->slug)->with('success','Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        $post->delete();
        return redirect()->route('blog.index')->with('success','Post deleted.');
    }
}
