<?php

use App\Enums\ContentSection;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ConcertEventsController;
use App\Http\Controllers\Content\CategoryController;
use App\Http\Controllers\Content\GalleryController;
use App\Http\Controllers\Content\PostController;
use App\Http\Controllers\Content\TimelineController;
use App\Http\Controllers\Content\TriviaController;
use App\Http\Controllers\CustomPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiveStreamingController;
use App\Http\Controllers\MagazineController;
use App\Http\Controllers\MeetGreetEventsController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\PublicMagazineController;
use App\Http\Controllers\PublicPhotoboothController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\PublicTimelineController;
use App\Http\Controllers\PublicTriviaController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShowTeaterCategoriesController;
use App\Http\Controllers\ShowTeaterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');

Route::get('about/fansite', [AboutController::class, 'fansite'])->name('about.fansite');
Route::get('about/{idol?}', [AboutController::class, 'idol'])->name('about.idol');

Route::get('majalah', [PublicMagazineController::class, 'index'])->middleware('feature:magazines')->name('magazine.index');
Route::get('majalah/{magazine:slug}', [PublicMagazineController::class, 'show'])->middleware('feature:magazines')->name('magazine.show');
Route::get('majalah/{magazine:slug}/download', [PublicMagazineController::class, 'download'])->middleware('feature:magazines')->name('magazine.download');

Route::get('news', [PublicPostController::class, 'index'])->defaults('section', 'news')->middleware('feature:news')->name('news.index');
Route::get('news/{post}', [PublicPostController::class, 'show'])->defaults('section', 'news')->middleware('feature:news')->name('news.show');
Route::get('blog', [PublicPostController::class, 'index'])->defaults('section', 'blog')->middleware('feature:blog')->name('blog.index');
Route::get('blog/{post}', [PublicPostController::class, 'show'])->defaults('section', 'blog')->middleware('feature:blog')->name('blog.show');

Route::get('schedule', ScheduleController::class)->name('schedule.index');

Route::get('galeri', [PublicGalleryController::class, 'index'])->name('gallery.index');

Route::get('timeline', [PublicTimelineController::class, 'index'])->name('timeline.index');

Route::get('trivia', [PublicTriviaController::class, 'index'])->middleware('feature:trivia')->name('trivia.index');

Route::get('photobooth/{slug?}', [PublicPhotoboothController::class, 'show'])
    ->middleware(['feature:photobooth', 'throttle:30,1'])
    ->name('photobooth.show');

Route::middleware(['auth', 'verified', 'block-view-only-writes'])->group(function () {
    Route::middleware('role:super_admin,view_only,bank_data_admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Show Teater
        Route::get('show-teater', [ShowTeaterController::class, 'index'])->name('show-teater.index');
        Route::post('show-teater', [ShowTeaterController::class, 'store'])->name('show-teater.store');
        Route::put('show-teater/{id}', [ShowTeaterController::class, 'update'])->name('show-teater.update');
        Route::post('show-teater/{id}/confirm', [ShowTeaterController::class, 'confirmMemberShow'])->name('show-teater.confirm');
        Route::post('show-teater/{id}/reject', [ShowTeaterController::class, 'rejectMemberShow'])->name('show-teater.reject');
        Route::post('show-teater/fetch', [ShowTeaterController::class, 'fetchManually'])->name('show-teater.fetch-manually');

        // Show Teater Categories
        Route::get('show-teater/categories', [ShowTeaterCategoriesController::class, 'index'])->name('show-teater.categories.index');
        Route::post('show-teater/categories', [ShowTeaterCategoriesController::class, 'store'])->name('show-teater.categories.store');
        Route::put('show-teater/categories/{id}', [ShowTeaterCategoriesController::class, 'update'])->name('show-teater.categories.update');
        Route::post('show-teater/categories/{id}/toggle-status', [ShowTeaterCategoriesController::class, 'toggleStatus'])->name('show-teater.categories.toggle-status');

        // Meet & Greet Events
        Route::get('meet-greet-events', [MeetGreetEventsController::class, 'index'])->name('meet-greet-events.index');
        Route::post('meet-greet-events', [MeetGreetEventsController::class, 'store'])->name('meet-greet-events.store');
        Route::put('meet-greet-events/{meetGreetEvent}', [MeetGreetEventsController::class, 'update'])->name('meet-greet-events.update');
        Route::delete('meet-greet-events/{meetGreetEvent}', [MeetGreetEventsController::class, 'destroy'])->name('meet-greet-events.destroy');

        // Concerts Events
        Route::get('concert-events', [ConcertEventsController::class, 'index'])->name('concert-events.index');
        Route::post('concert-events', [ConcertEventsController::class, 'store'])->name('concert-events.store');
        Route::put('concert-events/{concertEvent}', [ConcertEventsController::class, 'update'])->name('concert-events.update');
        Route::delete('concert-events/{concertEvent}', [ConcertEventsController::class, 'destroy'])->name('concert-events.destroy');

        // Live Streaming
        Route::get('live-streaming', [LiveStreamingController::class, 'index'])->name('live-streaming.index');
        Route::post('live-streaming', [LiveStreamingController::class, 'store'])->name('live-streaming.store');
        Route::post('live-streaming/fetch', [LiveStreamingController::class, 'fetchManually'])->name('live-streaming.fetch-manually');
        Route::put('live-streaming/{liveStreaming}', [LiveStreamingController::class, 'update'])->name('live-streaming.update');
    });

    Route::middleware('role:super_admin,view_only,content_creator')->group(function () {
        Route::get('pages', [CustomPageController::class, 'index'])->name('pages.index');
        Route::view('pages/create', 'custom-pages.admin')->name('pages.create');
        Route::get('pages/{customPage}/edit', [CustomPageController::class, 'edit'])->name('pages.edit');
        Route::delete('pages/{customPage}', [CustomPageController::class, 'destroy'])->name('pages.destroy');

        // Majalah
        Route::middleware('feature:magazines')->group(function (): void {
            Route::get('magazines', [MagazineController::class, 'index'])->name('magazines.index');
            Route::post('magazines', [MagazineController::class, 'store'])->name('magazines.store');
            Route::put('magazines/{magazine}', [MagazineController::class, 'update'])->name('magazines.update');
            Route::post('magazines/{magazine}/main', [MagazineController::class, 'setMain'])->name('magazines.set-main');
            Route::delete('magazines/{magazine}', [MagazineController::class, 'destroy'])->name('magazines.destroy');
        });

        // News & Blog (fitur sama, tabel berbeda)
        foreach (ContentSection::cases() as $contentSection) {
            Route::prefix('content/'.$contentSection->value)->name('content.'.$contentSection->value.'.')->middleware('feature:'.$contentSection->value)->group(function () use ($contentSection): void {
                Route::get('/', [PostController::class, 'index'])->defaults('section', $contentSection->value)->name('index');
                Route::get('create', [PostController::class, 'create'])->defaults('section', $contentSection->value)->name('create');
                Route::post('/', [PostController::class, 'store'])->defaults('section', $contentSection->value)->name('store');
                Route::get('{id}/edit', [PostController::class, 'edit'])->defaults('section', $contentSection->value)->name('edit');
                Route::put('{id}', [PostController::class, 'update'])->defaults('section', $contentSection->value)->name('update');
                Route::delete('{id}', [PostController::class, 'destroy'])->defaults('section', $contentSection->value)->name('destroy');
            });
        }

        // Kategori (News & Blog)
        Route::prefix('content/categories')->name('content.categories.')->middleware('feature:news,blog')->group(function (): void {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::put('{id}', [CategoryController::class, 'update'])->name('update');
            Route::delete('{id}', [CategoryController::class, 'destroy'])->name('destroy');
        });

        // Galeri (Foto & Video)
        Route::get('content/gallery', [GalleryController::class, 'index'])->name('content.gallery.index');
        Route::post('content/gallery/photos', [GalleryController::class, 'storePhoto'])->name('content.gallery.photos.store');
        Route::put('content/gallery/photos/{galleryPhoto}', [GalleryController::class, 'updatePhoto'])->name('content.gallery.photos.update');
        Route::delete('content/gallery/photos/{galleryPhoto}', [GalleryController::class, 'destroyPhoto'])->name('content.gallery.photos.destroy');
        Route::post('content/gallery/videos', [GalleryController::class, 'storeVideo'])->name('content.gallery.videos.store');
        Route::put('content/gallery/videos/{galleryVideo}', [GalleryController::class, 'updateVideo'])->name('content.gallery.videos.update');
        Route::delete('content/gallery/videos/{galleryVideo}', [GalleryController::class, 'destroyVideo'])->name('content.gallery.videos.destroy');

        // Timeline
        Route::get('content/timeline', [TimelineController::class, 'index'])->name('content.timeline.index');
        Route::post('content/timeline', [TimelineController::class, 'store'])->name('content.timeline.store');
        Route::put('content/timeline/{timeline}', [TimelineController::class, 'update'])->name('content.timeline.update');
        Route::delete('content/timeline/{timeline}', [TimelineController::class, 'destroy'])->name('content.timeline.destroy');

        // Trivia
        Route::middleware('feature:trivia')->group(function (): void {
            Route::get('content/trivia', [TriviaController::class, 'index'])->name('content.trivia.index');
            Route::post('content/trivia', [TriviaController::class, 'store'])->name('content.trivia.store');
            Route::put('content/trivia/{trivia}', [TriviaController::class, 'update'])->name('content.trivia.update');
            Route::delete('content/trivia/{trivia}', [TriviaController::class, 'destroy'])->name('content.trivia.destroy');
        });
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::livewire('data-management/sheet-integration', 'pages::sheet-integration.comparison')
            ->name('sheet-integration.comparison');
    });
});

require __DIR__.'/settings.php';

Route::get('{customPage:slug}', [CustomPageController::class, 'show'])->name('custom-pages.show');
