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
use App\Http\Controllers\ShowroomProxyController;
use App\Http\Controllers\ShowTeaterController;
use App\Http\Controllers\ShowTeaterCategoryController;
use Illuminate\Support\Facades\DB;

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

    // Get events for the next 7 days
    $startDate = now()->startOfDay();
    $endDate = now()->addDays(7)->endOfDay();

    $upcomingEvents = collect();

    // Get Show Teater events
    $showTeaterEvents = DB::table('show_teater')
        ->whereBetween('show_date', [$startDate, $endDate])
        ->get()
        ->map(fn($show) => [
            'type' => 'SHOW',
            'name' => $show->setlist,
            'date' => $show->show_date,
            'color' => 'bg-red-500',
        ]);

    // Get Concert Events
    $concertEvents = \App\Models\ConcertEvent::whereBetween('event_date', [$startDate, $endDate])
        ->get()
        ->map(fn($concert) => [
            'type' => $concert->status === 'on-air' ? 'ON-AIR' : 'OFF-AIR',
            'name' => $concert->event_name,
            'date' => $concert->event_date,
            'color' => $concert->status === 'on-air' ? 'bg-blue-500' : 'bg-green-500',
        ]);

    // Get Meet & Greet Events
    $meetGreetEvents = \App\Models\MeetGreetEvent::where(function($query) use ($startDate, $endDate) {
        $query->whereBetween('event_date', [$startDate, $endDate])
              ->orWhereBetween('event_date_2', [$startDate, $endDate]);
    })->get()->map(fn($event) => [
        'type' => $event->event_type === 'video-call' ? 'VIDEO CALL' : 'MEET & GREET',
        'name' => $event->event_name,
        'date' => $event->event_date,
        'color' => $event->event_type === 'video-call' ? 'bg-purple-500' : 'bg-pink-500',
    ]);

    // Get Meet & Greet ticket sale events
    $ticketSaleEvents = \App\Models\MeetGreetEvent::whereNotNull('ticket_sale_datetime')
        ->whereBetween('ticket_sale_datetime', [$startDate, $endDate])
        ->get()
        ->map(fn($event) => [
            'type' => 'TICKET SALE',
            'name' => 'Pembelian Tiket ' . $event->event_name,
            'date' => $event->ticket_sale_datetime,
            'color' => 'bg-yellow-500',
        ]);

    // Combine all events and sort by date
    $upcomingEvents = $upcomingEvents
        ->concat($showTeaterEvents)
        ->concat($concertEvents)
        ->concat($meetGreetEvents)
        ->concat($ticketSaleEvents)
        ->sortBy('date')
        ->values();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'latestPosts' => $latestPosts,
        'upcomingEvents' => $upcomingEvents,
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

// Showroom API proxy to avoid CORS issues
Route::get('/api/showroom/live/{roomId}', [ShowroomProxyController::class, 'getLiveStatus'])->name('showroom.live');

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

    // Show Teater management
    Route::get('/show-teater', [ShowTeaterController::class, 'index'])->name('show-teater.index');
    Route::post('/show-teater', [ShowTeaterController::class, 'store'])->name('show-teater.store');
    Route::put('/show-teater/{id}', [ShowTeaterController::class, 'update'])->name('show-teater.update');

    // Show Teater Categories management
    Route::get('/show-teater/categories', [ShowTeaterCategoryController::class, 'index'])->name('show-teater.categories.index');
    Route::post('/show-teater/categories', [ShowTeaterCategoryController::class, 'store'])->name('show-teater.categories.store');
    Route::put('/show-teater/categories/{id}', [ShowTeaterCategoryController::class, 'update'])->name('show-teater.categories.update');
    Route::delete('/show-teater/categories/{id}', [ShowTeaterCategoryController::class, 'destroy'])->name('show-teater.categories.destroy');

    // Concert Events management
    Route::get('/concert-events', [\App\Http\Controllers\ConcertEventController::class, 'index'])->name('concert-events.index');
    Route::post('/concert-events', [\App\Http\Controllers\ConcertEventController::class, 'store'])->name('concert-events.store');
    Route::put('/concert-events/{concertEvent}', [\App\Http\Controllers\ConcertEventController::class, 'update'])->name('concert-events.update');
    Route::delete('/concert-events/{concertEvent}', [\App\Http\Controllers\ConcertEventController::class, 'destroy'])->name('concert-events.destroy');

    // Meet & Greet Events management
    Route::get('/meet-greet', [\App\Http\Controllers\MeetGreetEventController::class, 'index'])->name('meet-greet.index');
    Route::post('/meet-greet', [\App\Http\Controllers\MeetGreetEventController::class, 'store'])->name('meet-greet.store');
    Route::put('/meet-greet/{meetGreet}', [\App\Http\Controllers\MeetGreetEventController::class, 'update'])->name('meet-greet.update');
    Route::delete('/meet-greet/{meetGreet}', [\App\Http\Controllers\MeetGreetEventController::class, 'destroy'])->name('meet-greet.destroy');

    // About Settings management
    Route::get('/about/settings', [\App\Http\Controllers\AboutSettingsController::class, 'edit'])->name('about.settings');
    Route::post('/about/settings', [\App\Http\Controllers\AboutSettingsController::class, 'update'])->name('about.settings.update');
    Route::delete('/about/settings/gallery/{index}', [\App\Http\Controllers\AboutSettingsController::class, 'deleteGalleryImage'])->name('about.settings.gallery.delete');
});

// Public About pages
Route::get('/about/idol/{slug?}', [\App\Http\Controllers\AboutController::class, 'idol'])->name('about.idol');
Route::get('/about/fanbase/{slug?}', [\App\Http\Controllers\AboutController::class, 'fanbase'])->name('about.fanbase');

// Public custom page view - MUST BE LAST as catch-all fallback
Route::get('/{page}', [\App\Http\Controllers\CustomPageController::class, 'show'])->name('pages.show');
