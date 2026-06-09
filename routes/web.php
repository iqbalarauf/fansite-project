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
use App\Models\LiveStreaming;
use App\Models\Setting;
use App\Models\AboutSetting;

/**
 * Parse show_date from various formats
 */
function parseShowDate($dateString)
{
    try {
        // Try yyyy-mm-dd format first
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return \Carbon\Carbon::createFromFormat('Y-m-d', $dateString);
        }

        // Try dd.mm.yyyy format
        if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dateString)) {
            $parts = explode('.', $dateString);
            return \Carbon\Carbon::createFromFormat('Y-m-d', $parts[2] . '-' . $parts[1] . '-' . $parts[0]);
        }

        // Try Indonesian format: "Jumat, 28 November 2024"
        $months = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
            'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
            'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
        ];

        foreach ($months as $monthName => $monthNum) {
            if (strpos($dateString, $monthName) !== false) {
                if (preg_match('/(\d{1,2})\s+' . $monthName . '\s+(\d{4})/', $dateString, $matches)) {
                    $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $year = $matches[2];
                    return \Carbon\Carbon::createFromFormat('Y-m-d', $year . '-' . $monthNum . '-' . $day);
                }
            }
        }

        return null;
    } catch (\Exception $e) {
        return null;
    }
}

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

    // Count live streaming by platform
    $showroomCount = LiveStreaming::where('platform', 'Showroom')->count();
    $idnAppCount = LiveStreaming::where('platform', 'IDN App')->count();

    // Get latest 5 published gallery photos for carousel
    $latestGallery = \App\Models\Gallery::where('is_published', true)
        ->where('type', 'photo')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get()
        ->map(fn($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'credit' => $item->credit,
            'image_path' => $item->image_path ? Storage::url($item->image_path) : null,
        ]);

    // Get app settings for homepage only (lazy loaded)
    $appSettings = Setting::allKeyValues();
    $homepageAppSettings = [
        'desc_app' => $appSettings['desc_app'] ?? null,
        'hero_image' => $appSettings['hero_image'] ?? null,
        'showroom_room_id' => $appSettings['showroom_room_id'] ?? '416491',
        'showroom_link' => $appSettings['showroom_link'] ?? 'https://www.showroom-live.com/',
        'hero_button_1_text' => $appSettings['hero_button_1_text'] ?? 'Info Lebih Lanjut',
        'hero_button_1_link' => $appSettings['hero_button_1_link'] ?? '/blog',
        'hero_button_2_text' => $appSettings['hero_button_2_text'] ?? 'Temukan Kami',
        'hero_button_2_link' => $appSettings['hero_button_2_link'] ?? '/blog',
        'show_youtube_playlist' => $appSettings['show_youtube_playlist'] ?? 'false',
        'youtube_playlist_url' => $appSettings['youtube_playlist_url'] ?? '',
        'show_gallery_carousel' => $appSettings['show_gallery_carousel'] ?? 'true',
    ];

    // Get teater statistics (lazy loaded, only for homepage)
    $today = now()->startOfDay();

    // Get the latest show_date that is before today
    $lastUpdateDate = DB::table('show_teater')
        ->get()
        ->filter(function ($show) use ($today) {
            $date = parseShowDate($show->show_date);
            return $date && $date->lt($today);
        })
        ->sortByDesc(function ($show) {
            return parseShowDate($show->show_date);
        })
        ->first()?->show_date;

    // Filter and count shows before today
    $pastShows = DB::table('show_teater')
        ->get()
        ->filter(function ($show) use ($today) {
            $date = parseShowDate($show->show_date);
            return $date && $date->lt($today);
        });

    // Count center and global center appearances
    $usCenterCount = DB::table('show_teater')
        ->where('is_us_center', 1)
        ->count();

    $globalCenterCount = DB::table('show_teater')
        ->where('is_global_center', 1)
        ->count();

    $uniqueSetlists = collect(DB::table('show_teater')->distinct()->pluck('setlist'))
        ->filter(fn($value) => $value !== null && $value !== '')
        ->values()
        ->count();

    $uniqueUnitSongs = collect(DB::table('show_teater')->pluck('unit_song'))
        ->flatMap(function ($unitSong) {
            if (!is_string($unitSong) || trim($unitSong) === '') {
                return [];
            }

            return collect(explode(',', $unitSong))
                ->map(fn($item) => preg_replace('/\s+/', ' ', trim($item)))
                ->filter();
        })
        ->unique()
        ->values()
        ->count();

    $teaterStats = [
        'total_shows' => $pastShows->count(),
        'unique_setlists' => $uniqueSetlists,
        'unique_unit_songs' => $uniqueUnitSongs,
        'us_center_count' => $usCenterCount,
        'global_center_count' => $globalCenterCount,
        'last_update' => $lastUpdateDate,
    ];

    // Get idol settings for homepage section (lazy loaded)
    $idolPhoto = AboutSetting::get('idol_photo', '');
    $homepageIdolSettings = [
        'idol_photo' => $idolPhoto ? Storage::url($idolPhoto) : null,
        'idol_description' => AboutSetting::get('idol_description', ''),
        'idol_social_media_instagram' => AboutSetting::get('idol_social_media_instagram', ''),
        'idol_social_media_tiktok' => AboutSetting::get('idol_social_media_tiktok', ''),
        'idol_social_media_twitter' => AboutSetting::get('idol_social_media_twitter', ''),
        'idol_show_on_welcome' => AboutSetting::get('idol_show_on_welcome', 'false'),
    ];

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'latestPosts' => $latestPosts,
        'upcomingEvents' => $upcomingEvents,
        'liveStreamingStats' => [
            'showroom_count' => $showroomCount,
            'idn_app_count' => $idnAppCount,
        ],
        'latestGallery' => $latestGallery,
        'homepageAppSettings' => $homepageAppSettings,
        'teaterStats' => $teaterStats,
        'homepageIdolSettings' => $homepageIdolSettings,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        // Get idol birthday from settings
        $idolBirthday = \App\Models\AboutSetting::get('idol_birth_date', null);
        $idolName = \App\Models\AboutSetting::get('idol_name', '');

        // Calculate next milestone (show teater in multiples of 100)
        $currentShowCount = DB::table('show_teater')->count();
        $nextMilestoneNumber = (floor($currentShowCount / 100) + 1) * 100;

        $nextMilestone = null;
        if ($currentShowCount > 0) {
            $nextMilestone = [
                'showNumber' => $nextMilestoneNumber,
                'currentCount' => $currentShowCount,
            ];
        }

        // Get teater statistics
        $usCenterCount = DB::table('show_teater')
            ->where('is_us_center', 1)
            ->count();

        $globalCenterCount = DB::table('show_teater')
            ->where('is_global_center', 1)
            ->count();

        $uniqueSetlists = collect(DB::table('show_teater')->distinct()->pluck('setlist'))
            ->filter(fn($value) => $value !== null && $value !== '')
            ->values()
            ->count();

        $uniqueUnitSongs = collect(DB::table('show_teater')->pluck('unit_song'))
            ->flatMap(function ($unitSong) {
                if (!is_string($unitSong) || trim($unitSong) === '') {
                    return [];
                }

                return collect(explode(',', $unitSong))
                    ->map(fn($item) => preg_replace('/\s+/', ' ', trim($item)))
                    ->filter();
            })
            ->unique()
            ->values()
            ->count();

        $teaterStats = [
            'total_shows' => $currentShowCount,
            'unique_setlists' => $uniqueSetlists,
            'unique_unit_songs' => $uniqueUnitSongs,
            'us_center_count' => $usCenterCount,
            'global_center_count' => $globalCenterCount,
        ];

        // Get last week's completed shows
        $lastWeekStart = now()->startOfWeek()->subWeek();
        $lastWeekEnd = now()->endOfWeek()->subWeek();

        $lastWeekShows = DB::table('show_teater')
            ->whereBetween('show_date', [$lastWeekStart, $lastWeekEnd])
            ->get()
            ->map(function ($show) {
                $date = parseShowDate($show->show_date);
                return [
                    'type' => 'show_teater',
                    'show_id' => $show->show_id,
                    'show_date' => $show->show_date,
                    'setlist' => $show->setlist,
                    'unit_song' => $show->unit_song,
                    'is_global_center' => $show->is_global_center,
                    'is_us_center' => $show->is_us_center,
                    'additional_information' => $show->additional_information,
                    'is_the_show_has_event' => $show->is_the_show_has_event,
                    'parsed_date' => $date ? $date->format('Y-m-d') : null,
                ];
            });

        // Get last week's concert events
        $lastWeekConcertEvents = \App\Models\ConcertEvent::whereBetween('event_date', [$lastWeekStart, $lastWeekEnd])
            ->get()
            ->map(function ($event) {
                return [
                    'type' => 'concert',
                    'id' => $event->id,
                    'event_name' => $event->event_name,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'location' => $event->location,
                    'status' => $event->status,
                    'parsed_date' => $event->event_date->format('Y-m-d'),
                ];
            });

        // Get last week's meet & greet events
        $lastWeekMeetGreetEvents = \App\Models\MeetGreetEvent::where(function($query) use ($lastWeekStart, $lastWeekEnd) {
            $query->whereBetween('event_date', [$lastWeekStart, $lastWeekEnd])
                  ->orWhereBetween('event_date_2', [$lastWeekStart, $lastWeekEnd]);
        })->get()->map(function ($event) {
            return [
                'type' => 'meet_greet',
                'id' => $event->id,
                'event_name' => $event->event_name,
                'event_type' => $event->event_type,
                'event_date' => $event->event_date->format('Y-m-d'),
                'event_date_2' => $event->event_date_2 ? $event->event_date_2->format('Y-m-d') : null,
                'location' => $event->event_type === 'video-call' ? 'Video Call' : 'TBD',
                'parsed_date' => $event->event_date->format('Y-m-d'),
            ];
        });

        // Combine last week's events and sort by date
        $lastWeekEvents = collect()
            ->concat($lastWeekShows)
            ->concat($lastWeekConcertEvents)
            ->concat($lastWeekMeetGreetEvents)
            ->sortBy('parsed_date')
            ->values();

        // Get this week's upcoming shows
        $thisWeekStart = now()->startOfWeek();
        $thisWeekEnd = now()->endOfWeek();

        $thisWeekShows = DB::table('show_teater')
            ->whereBetween('show_date', [$thisWeekStart, $thisWeekEnd])
            ->get()
            ->map(function ($show) {
                $date = parseShowDate($show->show_date);
                return [
                    'type' => 'show_teater',
                    'show_id' => $show->show_id,
                    'show_date' => $show->show_date,
                    'setlist' => $show->setlist,
                    'parsed_date' => $date ? $date->format('Y-m-d') : null,
                ];
            });

        // Get this week's concert events
        $thisWeekConcertEvents = \App\Models\ConcertEvent::whereBetween('event_date', [$thisWeekStart, $thisWeekEnd])
            ->get()
            ->map(function ($event) {
                return [
                    'type' => 'concert',
                    'id' => $event->id,
                    'event_name' => $event->event_name,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'location' => $event->location,
                    'status' => $event->status,
                    'parsed_date' => $event->event_date->format('Y-m-d'),
                ];
            });

        // Get this week's meet & greet events
        $thisWeekMeetGreetEvents = \App\Models\MeetGreetEvent::where(function($query) use ($thisWeekStart, $thisWeekEnd) {
            $query->whereBetween('event_date', [$thisWeekStart, $thisWeekEnd])
                  ->orWhereBetween('event_date_2', [$thisWeekStart, $thisWeekEnd]);
        })->get()->map(function ($event) {
            return [
                'type' => 'meet_greet',
                'id' => $event->id,
                'event_name' => $event->event_name,
                'event_type' => $event->event_type,
                'event_date' => $event->event_date->format('Y-m-d'),
                'event_date_2' => $event->event_date_2 ? $event->event_date_2->format('Y-m-d') : null,
                'location' => $event->event_type === 'video-call' ? 'Video Call' : 'TBD',
                'parsed_date' => $event->event_date->format('Y-m-d'),
            ];
        });

        // Combine this week's events and sort by date
        $thisWeekEvents = collect()
            ->concat($thisWeekShows)
            ->concat($thisWeekConcertEvents)
            ->concat($thisWeekMeetGreetEvents)
            ->sortBy('parsed_date')
            ->values();

        return Inertia::render('Dashboard', [
            'idolBirthday' => $idolBirthday,
            'idolName' => $idolName,
            'nextMilestone' => $nextMilestone,
            'teaterStats' => $teaterStats,
            'lastWeekEvents' => $lastWeekEvents,
            'thisWeekEvents' => $thisWeekEvents,
        ]);
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
    Route::post('/show-teater/{id}/confirm-member', [ShowTeaterController::class, 'confirmMemberShow'])->name('show-teater.confirm-member');
    Route::delete('/show-teater/{id}/reject-member', [ShowTeaterController::class, 'rejectMemberShow'])->name('show-teater.reject-member');
    Route::post('/show-teater/fetch/manual', [ShowTeaterController::class, 'fetchManually'])->name('show-teater.fetch-manual');

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

    // Live Streaming management
    Route::get('/live-streaming', [\App\Http\Controllers\LiveStreamingController::class, 'index'])->name('live-streaming.index');
    Route::post('/live-streaming', [\App\Http\Controllers\LiveStreamingController::class, 'store'])->name('live-streaming.store');
    Route::put('/live-streaming/{liveStreaming}', [\App\Http\Controllers\LiveStreamingController::class, 'update'])->name('live-streaming.update');
    Route::delete('/live-streaming/{liveStreaming}', [\App\Http\Controllers\LiveStreamingController::class, 'destroy'])->name('live-streaming.destroy');

    // Gallery management
    Route::get('/manage/gallery', [\App\Http\Controllers\GalleryController::class, 'index'])->name('gallery.index');
    Route::post('/manage/gallery', [\App\Http\Controllers\GalleryController::class, 'store'])->name('gallery.store');
    Route::put('/manage/gallery/{gallery}', [\App\Http\Controllers\GalleryController::class, 'update'])->name('gallery.update');
    Route::delete('/manage/gallery/{gallery}', [\App\Http\Controllers\GalleryController::class, 'destroy'])->name('gallery.destroy');

    // About Settings management
    Route::get('/about/settings', [\App\Http\Controllers\AboutSettingsController::class, 'edit'])->name('about.settings');
    Route::post('/about/settings', [\App\Http\Controllers\AboutSettingsController::class, 'update'])->name('about.settings.update');
    Route::delete('/about/settings/gallery/{index}', [\App\Http\Controllers\AboutSettingsController::class, 'deleteGalleryImage'])->name('about.settings.gallery.delete');
});

// Public About pages
Route::get('/about/idol/{slug?}', [\App\Http\Controllers\AboutController::class, 'idol'])->name('about.idol');
Route::get('/about/fanbase/{slug?}', [\App\Http\Controllers\AboutController::class, 'fanbase'])->name('about.fanbase');

// Public Gallery
Route::get('/gallery', [\App\Http\Controllers\PublicGalleryController::class, 'index'])->name('public.gallery');

// Error test routes
    Route::get('/_test/error/400', fn() => response()->json(['error' => 'Bad Request'], 400));
    Route::get('/_test/error/403', fn() => response()->json(['error' => 'Forbidden'], 403));
    Route::get('/_test/error/404', fn() => response()->json(['error' => 'Not Found'], 404));
    Route::get('/_test/error/500', function () {
        throw new \Exception('Test 500 error');
    });

// Public custom page view - MUST BE LAST as catch-all fallback
Route::get('/{page}', [\App\Http\Controllers\CustomPageController::class, 'show'])->name('pages.show');
