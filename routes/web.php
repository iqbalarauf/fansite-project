<?php

use App\Http\Controllers\ConcertEventsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiveStreamingController;
use App\Http\Controllers\MeetGreetEventsController;
use App\Http\Controllers\ShowTeaterCategoriesController;
use App\Http\Controllers\ShowTeaterController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
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
    Route::put('live-streaming/{liveStreaming}', [LiveStreamingController::class, 'update'])->name('live-streaming.update');
});

require __DIR__.'/settings.php';
