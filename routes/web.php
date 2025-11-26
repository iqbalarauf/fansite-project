<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PostController;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    $latestPosts = Post::where('status','published')
        ->whereNotNull('published_at')
        ->where('published_at','<=', now())
        ->orderByDesc('published_at')
        ->take(5)
        ->get()
        ->map(fn($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'excerpt' => $p->excerpt,
            'published_at' => $p->published_at,
            'thumbnail' => $p->featured_image ? Storage::url($p->featured_image) : null,
        ]);

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'latestPosts' => $latestPosts,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Blog public routes (Inertia)
use Illuminate\Support\Str;

Route::get('/blog', function () {
    $posts = Post::where('status','published')
        ->whereNotNull('published_at')
        ->where('published_at','<=', now())
        ->orderByDesc('published_at')
        ->paginate(10)
        ->through(fn($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'excerpt' => $p->excerpt,
            // include a thumbnail URL where available so the UI can show an image
            'thumbnail' => $p->featured_image ? Storage::url($p->featured_image) : null,
            // provide a trimmed preview of the body (120 chars) and whether there's more
            'summary' => $p->excerpt ?? Str::limit(strip_tags($p->body_html), 120, ''),
            'has_more' => strlen(strip_tags($p->body_html)) > 120,
            'published_at' => $p->published_at,
        ]);

    return Inertia::render('Blog/Index', [
        'posts' => $posts,
    ]);
})->name('blog.index');

Route::get('/blog/{post}', [PostController::class, 'show'])->name('blog.show');

// Auth protected post management (create/edit/delete)
Route::middleware(['auth'])->group(function () {
    Route::resource('posts', PostController::class)->except(['index','show']);
    // Settings management - restrict to users allowed by manage-settings gate
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->middleware('can:manage-settings')->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->middleware('can:manage-settings')->name('settings.update');
});

// Management page: only authenticated users can access their posts
Route::middleware(['auth'])->group(function () {
    Route::get('/posts', function () {
        $posts = auth()->user()->posts()->orderByDesc('updated_at')->paginate(20)->through(fn($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'status' => $p->status,
            'published_at' => $p->published_at,
        ]);

        return Inertia::render('Posts/Manage', [
            'posts' => $posts,
        ]);
    })->name('posts.manage');
});

// Settings (change app name)

