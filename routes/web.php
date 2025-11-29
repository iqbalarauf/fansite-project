<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AccountController;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SettingsController;

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

// Registration routes accessible to authenticated users for creating additional accounts.
// GET uses Fortify's registration page. POST is overridden to avoid switching auth.
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/register', [AccountController::class, 'register']);
});

// Auth protected post management (create/edit/delete)
Route::middleware(['auth'])->group(function () {
    Route::resource('posts', PostController::class)->except(['index','show']);
    Route::resource('pages', \App\Http\Controllers\CustomPageController::class)->except(['show']);

    // Editor uploads
    Route::post('/uploads/images', [\App\Http\Controllers\UploadController::class, 'storeImage'])->name('uploads.images');
});

// Public custom page view
Route::get('/page/{page}', [\App\Http\Controllers\CustomPageController::class, 'show'])->name('pages.show');

// Settings routes (auth + verified). Single canonical names:
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
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
            'category' => $p->category,
        ]);

        return Inertia::render('Posts/Manage', [
            'posts' => $posts,
        ]);
    })->name('posts.manage');

    // Accounts management
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.manage');
    Route::delete('/accounts/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');
});
