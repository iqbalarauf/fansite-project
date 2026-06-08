<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShowTeaterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('show-teater')->group(function () {
        Route::get('/', [ShowTeaterController::class, 'index'])->name('show-teater.index');
        Route::get('/create', [ShowTeaterController::class, 'create'])->name('show-teater.create');
        Route::post('/', [ShowTeaterController::class, 'store'])->name('show-teater.store');
        Route::get('/{id}/edit', [ShowTeaterController::class, 'edit'])->name('show-teater.edit');
        Route::put('/{id}', [ShowTeaterController::class, 'update'])->name('show-teater.update');
        Route::delete('/{id}', [ShowTeaterController::class, 'destroy'])->name('show-teater.destroy');
    });
});

require __DIR__.'/auth.php';
